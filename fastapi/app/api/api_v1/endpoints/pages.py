from typing import Any, List
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from uuid import UUID

from app import models, schemas
from app.db.session import get_db
from app.utils import security

router = APIRouter()

@router.get("/", response_model=List[schemas.Page])
def read_pages(
    skip: int = 0,
    limit: int = 100,
    db: Session = Depends(get_db),
) -> Any:
    """
    Retrieve published pages.
    """
    pages = db.query(models.Page).filter(models.Page.is_published == True).offset(skip).limit(limit).all()
    return pages

@router.post("/", response_model=schemas.Page)
def create_page(
    *,
    db: Session = Depends(get_db),
    page_in: schemas.PageCreate,
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Create new page.
    """
    # Check if page with the same slug already exists
    page = db.query(models.Page).filter(models.Page.slug == page_in.slug).first()
    if page:
        raise HTTPException(
            status_code=400,
            detail="A page with this slug already exists."
        )
    
    page = models.Page(
        **page_in.dict(),
        created_by=current_user.id
    )
    db.add(page)
    db.commit()
    db.refresh(page)
    return page

@router.get("/all", response_model=List[schemas.Page])
def read_all_pages(
    skip: int = 0,
    limit: int = 100,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Retrieve all pages, including unpublished ones (admin only).
    """
    pages = db.query(models.Page).offset(skip).limit(limit).all()
    return pages

@router.get("/{slug}", response_model=schemas.Page)
def read_page_by_slug(
    slug: str,
    db: Session = Depends(get_db),
) -> Any:
    """
    Get page by slug.
    """
    page = db.query(models.Page).filter(models.Page.slug == slug).first()
    if not page:
        raise HTTPException(status_code=404, detail="Page not found")
    
    if not page.is_published:
        # If page is not published, only admins can view it
        try:
            current_user = security.get_current_active_user()
            if not current_user.is_admin:
                raise HTTPException(status_code=404, detail="Page not found")
        except:
            raise HTTPException(status_code=404, detail="Page not found")
    
    return page

@router.put("/{page_id}", response_model=schemas.Page)
def update_page(
    *,
    db: Session = Depends(get_db),
    page_id: UUID,
    page_in: schemas.PageUpdate,
    current_user: models.User = Depends(security.get_current_active_superuser),
) -> Any:
    """
    Update a page.
    """
    page = db.query(models.Page).filter(models.Page.id == page_id).first()
    if not page:
        raise HTTPException(status_code=404, detail="Page not found")
    
    if page_in.slug:
        # Check that the new slug doesn't conflict with another page
        existing_page = db.query(models.Page).filter(
            models.Page.slug == page_in.slug,
            models.Page.id != page_id
        ).first()
        if existing_page:
            raise HTTPException(
                status_code=400,
                detail="A page with this slug already exists."
            )
    
    update_data = page_in.dict(exclude_unset=True)
    
    for field, value in update_data.items():
        setattr(page, field, value)
    
    db.add(page)
    db.commit()
    db.refresh(page)
    return page