from pydantic import BaseModel, validator
from typing import Optional, List
from uuid import UUID
from datetime import date, datetime

from app.models.membership import (
    Gender, 
    MaritalStatus, 
    Math, 
    MembershipType, 
    MembershipStatus
)

class MembershipBase(BaseModel):
    user_id: UUID
    gender: Gender
    postal_address: str
    pin_code: str
    date_of_birth: date
    occupation: str
    qualification: str
    marital_status: MaritalStatus
    number_of_kids: Optional[int] = None
    gotra: str
    kuladevata: str
    math: Math
    native_place: str
    other_gsb_memberships: Optional[str] = None
    introducer_name: Optional[str] = None

    @validator('pin_code')
    def validate_pin_code(cls, v):
        if not v.isdigit() or len(v) != 6:
            raise ValueError("Pin code must be 6 digits")
        return v
    
    @validator('number_of_kids')
    def validate_number_of_kids(cls, v, values):
        if v is not None and v < 0:
            raise ValueError("Number of kids cannot be negative")
        if values.get('marital_status') == MaritalStatus.UNMARRIED and v not in [None, 0]:
            raise ValueError("Unmarried members should not have children")
        return v

class MembershipCreate(MembershipBase):
    membership_type: MembershipType = MembershipType.PATRON
    status: MembershipStatus = MembershipStatus.PENDING

class MembershipUpdate(BaseModel):
    gender: Optional[Gender] = None
    postal_address: Optional[str] = None
    pin_code: Optional[str] = None
    date_of_birth: Optional[date] = None
    occupation: Optional[str] = None
    qualification: Optional[str] = None
    marital_status: Optional[MaritalStatus] = None
    number_of_kids: Optional[int] = None
    gotra: Optional[str] = None
    kuladevata: Optional[str] = None
    math: Optional[Math] = None
    native_place: Optional[str] = None
    other_gsb_memberships: Optional[str] = None
    introducer_name: Optional[str] = None
    membership_type: Optional[MembershipType] = None
    status: Optional[MembershipStatus] = None
    approval_date: Optional[datetime] = None

class Membership(MembershipBase):
    id: UUID
    membership_type: MembershipType
    status: MembershipStatus
    application_date: datetime
    approval_date: Optional[datetime] = None

    class Config:
        orm_mode = True