from typing import Any, List
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from uuid import UUID

from app import models, schemas
from app.db.session import get_db
from app.utils import security

router = APIRouter()

@router.get("/", response_model=List[schemas.Seva])
def read_sevas(
    skip: int = 0,
    limit: int = 100,
    db: Session = Depends(get_db),
) -> Any:
    """
    Retrieve all active sevas.
    """
    sevas = db.query(models.Seva).filter(models.Seva.is_active == True).offset(skip).limit(limit).all()
    return sevas

@router.post("/", response_model=schemas.Seva)
def create_seva(
    *,
    db: Session = Depends(get_db),
    seva_in: schemas.SevaCreate,
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Create new seva.
    """
    seva = db.query(models.Seva).filter(models.Seva.name == seva_in.name).first()
    if seva:
        raise HTTPException(
            status_code=400,
            detail="A seva with this name already exists."
        )
    
    seva = models.Seva(**seva_in.dict())
    db.add(seva)
    db.commit()
    db.refresh(seva)
    return seva

@router.get("/{seva_id}", response_model=schemas.Seva)
def read_seva(
    seva_id: UUID,
    db: Session = Depends(get_db),
) -> Any:
    """
    Get seva by ID.
    """
    seva = db.query(models.Seva).filter(models.Seva.id == seva_id).first()
    if not seva:
        raise HTTPException(status_code=404, detail="Seva not found")
    return seva

@router.put("/{seva_id}", response_model=schemas.Seva)
def update_seva(
    *,
    db: Session = Depends(get_db),
    seva_id: UUID,
    seva_in: schemas.SevaUpdate,
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Update a seva.
    """
    seva = db.query(models.Seva).filter(models.Seva.id == seva_id).first()
    if not seva:
        raise HTTPException(status_code=404, detail="Seva not found")
    
    update_data = seva_in.dict(exclude_unset=True)
    
    for field, value in update_data.items():
        setattr(seva, field, value)
    
    db.add(seva)
    db.commit()
    db.refresh(seva)
    return seva