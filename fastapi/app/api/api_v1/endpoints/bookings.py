from typing import Any, List
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from uuid import UUID

from app import models, schemas
from app.db.session import get_db
from app.utils import security

router = APIRouter()

@router.get("/", response_model=List[schemas.Booking])
def read_bookings(
    skip: int = 0,
    limit: int = 100,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(security.get_current_active_user),
) -> Any:
    """
    Retrieve bookings.
    """
    # Admin can see all bookings
    if current_user.is_admin:
        bookings = db.query(models.Booking).offset(skip).limit(limit).all()
    else:
        # Regular users can only see their own bookings
        bookings = db.query(models.Booking).filter(
            models.Booking.user_id == current_user.id
        ).offset(skip).limit(limit).all()
    
    return bookings

@router.post("/", response_model=schemas.Booking)
def create_booking(
    *,
    db: Session = Depends(get_db),
    booking_in: schemas.BookingCreate,
    current_user: models.User = Depends(security.get_current_active_user),
) -> Any:
    """
    Create new booking.
    """
    # Use current user ID if not specified (and not admin)
    user_id = booking_in.user_id if current_user.is_admin and booking_in.user_id else current_user.id
    
    # Make sure the user exists
    user = db.query(models.User).filter(models.User.id == user_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    
    # Create the booking
    booking = models.Booking(
        user_id=user_id,
        total_amount=booking_in.total_amount,
        donation_amount=booking_in.donation_amount,
        pan_number=booking_in.pan_number,
        payment_status=booking_in.payment_status,
        receipt_id=booking_in.receipt_id,
        payment_gateway_ref=booking_in.payment_gateway_ref
    )
    db.add(booking)
    db.commit()
    db.refresh(booking)
    
    # Add booking items if provided
    if booking_in.items:
        for item_data in booking_in.items:
            # Verify the seva exists
            seva = db.query(models.Seva).filter(models.Seva.id == item_data.seva_id).first()
            if not seva:
                raise HTTPException(status_code=404, detail=f"Seva with ID {item_data.seva_id} not found")
            
            booking_item = models.BookingItem(
                booking_id=booking.id,
                seva_id=item_data.seva_id,
                quantity=item_data.quantity,
                price_at_booking=item_data.price_at_booking or seva.price
            )
            db.add(booking_item)
        
        db.commit()
    
    return booking

@router.get("/{booking_id}", response_model=schemas.Booking)
def read_booking(
    booking_id: UUID,
    db: Session = Depends(get_db),
    current_user: models.User = Depends(security.get_current_active_user),
) -> Any:
    """
    Get booking by ID.
    """
    booking = db.query(models.Booking).filter(models.Booking.id == booking_id).first()
    if not booking:
        raise HTTPException(status_code=404, detail="Booking not found")
    
    # Regular users can only see their own bookings
    if not current_user.is_admin and booking.user_id != current_user.id:
        raise HTTPException(status_code=403, detail="Not enough permissions")
    
    return booking