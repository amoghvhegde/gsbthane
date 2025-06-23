from pydantic import BaseSettings, PostgresDsn
from typing import Optional
import secrets

class Settings(BaseSettings):
    API_V1_STR: str = "/api/v1"
    PROJECT_NAME: str = "GSB Mandal Thane API"
    
    # PostgreSQL database settings
    POSTGRES_SERVER: str = "localhost"
    POSTGRES_USER: str = "postgres"
    POSTGRES_PASSWORD: str = "postgres"
    POSTGRES_DB: str = "gsb_mandal"
    SQLALCHEMY_DATABASE_URI: Optional[PostgresDsn] = None
    
    # Security settings
    SECRET_KEY: str = secrets.token_urlsafe(32)
    # 60 minutes * 24 hours * 8 days = 8 days
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 60 * 24 * 8
    
    class Config:
        case_sensitive = True
        env_file = ".env"

settings = Settings()

# Construct PostgreSQL URI
if settings.POSTGRES_SERVER and settings.POSTGRES_USER and settings.POSTGRES_PASSWORD and settings.POSTGRES_DB:
    settings.SQLALCHEMY_DATABASE_URI = PostgresDsn.build(
        scheme="postgresql",
        user=settings.POSTGRES_USER,
        password=settings.POSTGRES_PASSWORD,
        host=settings.POSTGRES_SERVER,
        path=f"/{settings.POSTGRES_DB}"
    )