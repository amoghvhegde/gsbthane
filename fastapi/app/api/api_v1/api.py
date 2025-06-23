from fastapi import APIRouter
from app.api.api_v1.endpoints import users, memberships, sevas, bookings, pages

api_router = APIRouter()
api_router.include_router(users.router, prefix="/users", tags=["users"])
api_router.include_router(memberships.router, prefix="/memberships", tags=["memberships"])
api_router.include_router(sevas.router, prefix="/sevas", tags=["sevas"])
api_router.include_router(bookings.router, prefix="/bookings", tags=["bookings"])
api_router.include_router(pages.router, prefix="/pages", tags=["pages"])