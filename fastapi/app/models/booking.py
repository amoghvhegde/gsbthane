from sqlalchemy import Column, ForeignKey, String, Text, Numeric, Integer, DateTime, Enum
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import enum
import uuid

from app.db.base_class import Base

class PaymentStatus(str, enum.Enum):
    PENDING = "PENDING"
    COMPLETED = "COMPLETED"
    FAILED = "FAILED"

class Booking(Base):
    """
    Booking model - represents a donation/booking transaction
    """
    # User making the booking
    user_id = Column(ForeignKey("user.id"), nullable=False)
    user = relationship("User", backref="bookings")
    
    # Booking details
    booking_date = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    total_amount = Column(Numeric(10, 2), nullable=False)
    donation_amount = Column(Numeric(10, 2), nullable=False, default=0)
    
    # Payment details
    pan_number = Column(String(10), nullable=True)
    payment_status = Column(
        Enum(PaymentStatus, name="payment_status_enum"),
        nullable=False,
        default=PaymentStatus.PENDING
    )
    receipt_id = Column(String, nullable=True, unique=True)
    payment_gateway_ref = Column(String, nullable=True)
    
    # Timestamp
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    
    # Relationships
    items = relationship("BookingItem", back_populates="booking", cascade="all, delete-orphan")

class BookingItem(Base):
    """
    BookingItem model - represents individual seva items within a booking
    """
    # Booking reference
    booking_id = Column(ForeignKey("booking.id", ondelete="CASCADE"), nullable=False)
    booking = relationship("Booking", back_populates="items")
    
    # Seva reference
    seva_id = Column(ForeignKey("seva.id"), nullable=False)
    seva = relationship("Seva", back_populates="booking_items")
    
    # Details
    quantity = Column(Integer, nullable=False, default=1)
    price_at_booking = Column(Numeric(10, 2), nullable=False)