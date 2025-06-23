from fastapi import FastAPI, Depends, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import OAuth2PasswordRequestForm
from datetime import timedelta
from sqlalchemy.orm import Session

from app.api.api_v1.api import api_router
from app.core.config import settings
from app.db.session import get_db, engine
from app.db.base_class import Base
from app.models import user, membership, seva, booking, page
from app.utils import security

# Create all tables in the database
Base.metadata.create_all(bind=engine)

app = FastAPI(
    title=settings.PROJECT_NAME,
    openapi_url=f"{settings.API_V1_STR}/openapi.json"
)

# Set up CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # For production, replace with specific origins
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include API router
app.include_router(api_router, prefix=settings.API_V1_STR)

@app.get("/")
async def root():
    return {"message": f"Welcome to {settings.PROJECT_NAME}!"}

@app.get("/health")
async def health_check():
    return {"status": "healthy"}

# Authentication endpoint
@app.post(f"{settings.API_V1_STR}/auth/login", tags=["Authentication"])
async def login(
    db: Session = Depends(get_db),
    form_data: OAuth2PasswordRequestForm = Depends()
):
    """
    OAuth2 compatible token login, get an access token for future requests
    """
    user = db.query(user.User).filter(user.User.email == form_data.username).first()
    if not user or not security.verify_password(form_data.password, user.password_hash):
        raise HTTPException(
            status_code=400,
            detail="Incorrect username or password"
        )
    
    access_token_expires = timedelta(minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = security.create_access_token(
        subject=user.id, expires_delta=access_token_expires
    )
    return {
        "access_token": access_token,
        "token_type": "bearer"
    }

# Optional: Data import endpoint for admin use
@app.post(f"{settings.API_V1_STR}/admin/import-data", tags=["Administration"])
async def import_data(
    members_csv_path: str,
    address_csv_path: str,
    db: Session = Depends(get_db),
    current_user: user.User = Depends(security.get_current_active_superuser)
):
    """
    Import member data from CSV files (admin only)
    """
    from app.utils.import_data import import_members_from_csv
    
    try:
        users_created, memberships_created, errors = import_members_from_csv(
            db, members_csv_path, address_csv_path
        )
        
        return {
            "success": True,
            "users_created": users_created,
            "memberships_created": memberships_created,
            "errors": errors
        }
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error importing data: {str(e)}"
        )