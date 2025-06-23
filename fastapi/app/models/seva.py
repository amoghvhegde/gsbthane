from sqlalchemy import Column, String, Text, Numeric, Boolean
from sqlalchemy.orm import relationship
import uuid

from app.db.base_class import Base

class Seva(Base):
    """
    Seva model - represents pooja/seva offerings provided by the Mandal
    """
    # Seva details
    name = Column(String, nullable=False, unique=True)
    description = Column(Text, nullable=True)
    
    # Financial details
    price = Column(Numeric(10, 2), nullable=False)
    
    # Status
    is_active = Column(Boolean, nullable=False, default=True)
    
    # Relationships
    booking_items = relationship("BookingItem", back_populates="seva")