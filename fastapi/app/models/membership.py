from sqlalchemy import Column, String, Text, Integer, ForeignKey, Enum, Date, DateTime
from sqlalchemy.orm import relationship
from sqlalchemy.sql import func
import enum
import uuid

from app.db.base_class import Base

class Gender(str, enum.Enum):
    MALE = "MALE"
    FEMALE = "FEMALE"

class MaritalStatus(str, enum.Enum):
    MARRIED = "MARRIED"
    UNMARRIED = "UNMARRIED"

class Math(str, enum.Enum):
    KASHI = "KASHI"
    GOKARNA = "GOKARNA"
    KAVALE = "KAVALE"

class MembershipType(str, enum.Enum):
    PATRON = "PATRON"
    # Add other types as needed

class MembershipStatus(str, enum.Enum):
    PENDING = "PENDING"
    APPROVED = "APPROVED"
    REJECTED = "REJECTED"

class Membership(Base):
    """
    Membership model - represents detailed information for registered members
    """
    user_id = Column(
        ForeignKey("user.id", ondelete="CASCADE"),
        unique=True,
        nullable=False,
        index=True
    )
    user = relationship("User", backref="membership")
    
    # Personal Details
    gender = Column(Enum(Gender, name="gender_enum"), nullable=False)
    postal_address = Column(Text, nullable=False)
    pin_code = Column(String(6), nullable=False)
    date_of_birth = Column(Date, nullable=False)
    occupation = Column(String, nullable=False)
    qualification = Column(String, nullable=False)
    
    # Family Details
    marital_status = Column(Enum(MaritalStatus, name="marital_status_enum"), nullable=False)
    number_of_kids = Column(Integer, nullable=True)
    
    # GSB Specific Details
    gotra = Column(String, nullable=False)
    kuladevata = Column(String, nullable=False)
    math = Column(Enum(Math, name="math_enum"), nullable=False)
    native_place = Column(String, nullable=False)
    other_gsb_memberships = Column(Text, nullable=True)
    
    # Membership Details
    introducer_name = Column(String, nullable=True)
    membership_type = Column(
        Enum(MembershipType, name="membership_type_enum"),
        nullable=False,
        default=MembershipType.PATRON
    )
    status = Column(
        Enum(MembershipStatus, name="membership_status_enum"),
        nullable=False,
        default=MembershipStatus.PENDING
    )
    application_date = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    approval_date = Column(DateTime(timezone=True), nullable=True)