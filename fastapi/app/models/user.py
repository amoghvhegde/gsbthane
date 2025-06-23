from sqlalchemy import Column, String, Boolean, Enum, DateTime
from sqlalchemy.sql import func
import enum
import uuid

from app.db.base_class import Base

class UserType(str, enum.Enum):
    MEMBER = "M"
    NON_MEMBER = "NM"

class User(Base):
    """
    User model - represents members, non-members, and administrators
    """
    # Basic user information
    first_name = Column(String, nullable=False)
    middle_name = Column(String, nullable=True)
    surname = Column(String, nullable=False)
    email = Column(String, nullable=False, unique=True, index=True)
    password_hash = Column(String, nullable=True)  # Only used for admin login
    mobile_no = Column(String, nullable=False)
    
    # User type
    user_type = Column(
        Enum(UserType, name="user_type_enum"),
        nullable=False,
        default=UserType.NON_MEMBER
    )
    is_admin = Column(Boolean, nullable=False, default=False)
    
    # Timestamps
    created_at = Column(DateTime(timezone=True), server_default=func.now(), nullable=False)
    updated_at = Column(
        DateTime(timezone=True),
        server_default=func.now(),
        onupdate=func.now(),
        nullable=False
    )