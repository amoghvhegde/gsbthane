from pydantic import BaseModel, validator, condecimal
from typing import Optional, List
from uuid import UUID
from datetime import datetime

from app.models.booking import PaymentStatus

class BookingItemBase(BaseModel):
    seva_id: UUID
    quantity: int = 1
    price_at_booking: Optional[condecimal(decimal_places=2, ge=0)] = None

    @validator('quantity')
    def validate_quantity(cls, v):
        if v < 1:
            raise ValueError("Quantity must be at least 1")
        return v

class BookingItemCreate(BookingItemBase):
    pass

class BookingItem(BookingItemBase):
    id: UUID
    booking_id: UUID

    class Config:
        orm_mode = True

class BookingBase(BaseModel):
    user_id: Optional[UUID] = None  # Optional since it could be the current user
    total_amount: condecimal(decimal_places=2, ge=0)
    donation_amount: Optional[condecimal(decimal_places=2, ge=0)] = 0
    pan_number: Optional[str] = None
    payment_status: PaymentStatus = PaymentStatus.PENDING
    receipt_id: Optional[str] = None
    payment_gateway_ref: Optional[str] = None

    @validator('pan_number')
    def validate_pan(cls, v, values):
        # PAN validation only if amount is large or provided
        if v is not None:
            if not v.isalnum() or len(v) != 10:
                raise ValueError("PAN number must be 10 alphanumeric characters")
        return v

class BookingCreate(BookingBase):
    items: Optional[List[BookingItemCreate]] = None

class BookingUpdate(BaseModel):
    donation_amount: Optional[condecimal(decimal_places=2, ge=0)] = None
    pan_number: Optional[str] = None
    payment_status: Optional[PaymentStatus] = None
    receipt_id: Optional[str] = None
    payment_gateway_ref: Optional[str] = None

class Booking(BookingBase):
    id: UUID
    booking_date: datetime
    created_at: datetime
    items: List[BookingItem] = []

    class Config:
        orm_mode = True