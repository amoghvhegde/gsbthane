from pydantic import BaseModel, EmailStr, Field, validator
from typing import Optional, List
from uuid import UUID
from datetime import datetime

from app.models.user import UserType

class UserBase(BaseModel):
    first_name: str
    middle_name: Optional[str] = None
    surname: str
    email: EmailStr
    mobile_no: str
    user_type: UserType = UserType.NON_MEMBER
    is_admin: bool = False

class UserCreate(UserBase):
    password: Optional[str] = None
    
    @validator("mobile_no")
    def validate_mobile(cls, v):
        if not v.isdigit() or len(v) != 10:
            raise ValueError("Mobile number must be 10 digits")
        return v

class UserUpdate(BaseModel):
    first_name: Optional[str] = None
    middle_name: Optional[str] = None
    surname: Optional[str] = None
    email: Optional[EmailStr] = None
    mobile_no: Optional[str] = None
    user_type: Optional[UserType] = None
    is_admin: Optional[bool] = None
    password: Optional[str] = None

class User(UserBase):
    id: UUID
    created_at: datetime
    updated_at: datetime

    class Config:
        orm_mode = True