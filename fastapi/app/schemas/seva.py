from pydantic import BaseModel, validator, condecimal
from typing import Optional, List
from uuid import UUID

class SevaBase(BaseModel):
    name: str
    description: Optional[str] = None
    price: condecimal(decimal_places=2, ge=0)
    is_active: bool = True

class SevaCreate(SevaBase):
    pass

class SevaUpdate(BaseModel):
    name: Optional[str] = None
    description: Optional[str] = None
    price: Optional[condecimal(decimal_places=2, ge=0)] = None
    is_active: Optional[bool] = None

class Seva(SevaBase):
    id: UUID

    class Config:
        orm_mode = True