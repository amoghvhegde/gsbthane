# Import all the required SQLAlchemy modules
from sqlalchemy.ext.declarative import declarative_base, declared_attr
from sqlalchemy import Column, UUID
import uuid

# Create a base class for all models to inherit from
class Base:
    # Generate __tablename__ automatically
    @declared_attr
    def __tablename__(cls) -> str:
        return cls.__name__.lower()
    
    # Common columns for all tables
    id = Column(
        UUID(as_uuid=True), 
        primary_key=True, 
        default=uuid.uuid4, 
        nullable=False,
        index=True
    )

# Create the declarative base used by SQLAlchemy
Base = declarative_base(cls=Base)