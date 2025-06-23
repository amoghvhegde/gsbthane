from typing import Any, List
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from uuid import UUID

from app import models, schemas
from app.db.session import get_db
from app.utils import security

router = APIRouter()

@router.get("/", response_model=List[schemas.User])
def read_users(
    skip: int = 0,
    limit: int = 100,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Retrieve users.
    """
    users = db.query(models.User).offset(skip).limit(limit).all()
    return users

@router.post("/", response_model=schemas.User)
def create_user(
    *,
    db: Session = Depends(get_db),
    user_in: schemas.UserCreate,
) -> Any:
    """
    Create new user.
    """
    user = db.query(models.User).filter(models.User.email == user_in.email).first()
    if user:
        raise HTTPException(
            status_code=400,
            detail="A user with this email already exists."
        )
    user = models.User(
        first_name=user_in.first_name,
        middle_name=user_in.middle_name,
        surname=user_in.surname,
        email=user_in.email,
        mobile_no=user_in.mobile_no,
        user_type=user_in.user_type,
        is_admin=user_in.is_admin,
    )
    if user_in.password:
        user.password_hash = security.get_password_hash(user_in.password)
    db.add(user)
    db.commit()
    db.refresh(user)
    return user

@router.get("/{user_id}", response_model=schemas.User)
def read_user(
    user_id: UUID,
    db: Session = Depends(get_db),
) -> Any:
    """
    Get user by ID.
    """
    user = db.query(models.User).filter(models.User.id == user_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    return user

@router.put("/{user_id}", response_model=schemas.User)
def update_user(
    *,
    db: Session = Depends(get_db),
    user_id: UUID,
    user_in: schemas.UserUpdate,
    current_user: models.User = Depends(security.get_current_active_user),
) -> Any:
    """
    Update a user.
    """
    user = db.query(models.User).filter(models.User.id == user_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    
    if user.id != current_user.id and not current_user.is_admin:
        raise HTTPException(status_code=403, detail="Not enough permissions")
    
    update_data = user_in.dict(exclude_unset=True)
    
    if "password" in update_data and update_data["password"]:
        update_data["password_hash"] = security.get_password_hash(update_data["password"])
        del update_data["password"]
    
    for field, value in update_data.items():
        setattr(user, field, value)
    
    db.add(user)
    db.commit()
    db.refresh(user)
    return user