from pydantic import BaseModel
from typing import Optional
from uuid import UUID
from datetime import datetime

class PageBase(BaseModel):
    title: str
    slug: str
    content: str
    is_published: bool = False

class PageCreate(PageBase):
    pass

class PageUpdate(BaseModel):
    title: Optional[str] = None
    slug: Optional[str] = None
    content: Optional[str] = None
    is_published: Optional[bool] = None

class Page(PageBase):
    id: UUID
    created_by: UUID
    created_at: datetime
    updated_at: datetime

    class Config:
        orm_mode = True