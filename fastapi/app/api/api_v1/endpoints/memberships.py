from typing import Any, List
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from uuid import UUID

from app import models, schemas
from app.db.session import get_db
from app.utils import security

router = APIRouter()

@router.get("/", response_model=List[schemas.Membership])
def read_memberships(
    skip: int = 0,
    limit: int = 100,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Retrieve all memberships.
    """
    memberships = db.query(models.Membership).offset(skip).limit(limit).all()
    return memberships

@router.post("/", response_model=schemas.Membership)
def create_membership(
    *,
    db: Session = Depends(get_db),
    membership_in: schemas.MembershipCreate,
    current_user: models.User = Depends(security.get_current_active_user),
) -> Any:
    """
    Create new membership.
    """
    # Check if the user already has a membership
    existing_membership = db.query(models.Membership).filter(
        models.Membership.user_id == membership_in.user_id
    ).first()
    if existing_membership:
        raise HTTPException(
            status_code=400,
            detail="This user already has a membership."
        )
    
    # Make sure the referenced user exists
    user = db.query(models.User).filter(models.User.id == membership_in.user_id).first()
    if not user:
        raise HTTPException(
            status_code=404,
            detail="User not found"
        )
    
    # Create the membership
    membership = models.Membership(
        **membership_in.dict()
    )
    db.add(membership)
    db.commit()
    db.refresh(membership)
    return membership

@router.get("/{membership_id}", response_model=schemas.Membership)
def read_membership(
    membership_id: UUID,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(security.get_current_active_user),
) -> Any:
    """
    Get membership by ID.
    """
    membership = db.query(models.Membership).filter(models.Membership.id == membership_id).first()
    if not membership:
        raise HTTPException(status_code=404, detail="Membership not found")
    
    # Regular users can only see their own membership
    if not current_user.is_admin and membership.user_id != current_user.id:
        raise HTTPException(status_code=403, detail="Not enough permissions")
    
    return membership

@router.put("/{membership_id}", response_model=schemas.Membership)
def update_membership(
    *,
    db: Session = Depends(get_db),
    membership_id: UUID,
    membership_in: schemas.MembershipUpdate,
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Update a membership.
    """
    membership = db.query(models.Membership).filter(models.Membership.id == membership_id).first()
    if not membership:
        raise HTTPException(status_code=404, detail="Membership not found")
    
    update_data = membership_in.dict(exclude_unset=True)
    
    for field, value in update_data.items():
        setattr(membership, field, value)
    
    db.add(membership)
    db.commit()
    db.refresh(membership)
    return membership

@router.get("/user/{user_id}", response_model=schemas.Membership)
def get_membership_by_user(
    user_id: UUID,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(security.get_current_active_user),
) -> Any:
    """
    Get a user's membership.
    """
    # Regular users can only see their own membership
    if not current_user.is_admin and user_id != current_user.id:
        raise HTTPException(status_code=403, detail="Not enough permissions")
    
    membership = db.query(models.Membership).filter(models.Membership.user_id == user_id).first()
    if not membership:
        raise HTTPException(status_code=404, detail="Membership not found for this user")
    
    return membership