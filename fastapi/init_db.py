#!/usr/bin/env python3
import os
import sys
import argparse
import logging
from sqlalchemy.exc import SQLAlchemyError

# Add the current directory to the path so we can import app modules
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.db.session import engine, SessionLocal
from app.db.base_class import Base
from app.models import user, membership, seva, booking, page
from app.utils.import_data import import_members_from_csv
from app.utils.security import get_password_hash
from app.models.user import UserType

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def init_db(csv_import=False, members_csv=None, address_csv=None):
    try:
        # Create tables
        logger.info("Creating database tables...")
        Base.metadata.create_all(bind=engine)
        logger.info("Database tables created successfully!")
        
        # Create initial admin account
        db = SessionLocal()
        try:
            # Check if admin already exists
            admin_user = db.query(user.User).filter(user.User.is_admin == True).first()
            if not admin_user:
                logger.info("Creating admin user...")
                admin = user.User(
                    first_name="Admin",
                    middle_name=None,
                    surname="User",
                    email="admin@gsbmandal.org",
                    password_hash=get_password_hash("admin123"),  # Change this in production!
                    mobile_no="9999999999",
                    user_type=UserType.MEMBER,
                    is_admin=True
                )
                db.add(admin)
                db.commit()
                logger.info("Admin user created successfully!")
            else:
                logger.info("Admin user already exists")

            # Import data from CSV if specified
            if csv_import:
                if not members_csv or not address_csv:
                    logger.error("CSV import requested but file paths not provided")
                    return
                
                if not os.path.exists(members_csv):
                    logger.error(f"Members CSV file not found: {members_csv}")
                    return
                
                if not os.path.exists(address_csv):
                    logger.error(f"Address CSV file not found: {address_csv}")
                    return
                
                logger.info("Importing member data from CSV files...")
                users_created, memberships_created, errors = import_members_from_csv(
                    db, members_csv, address_csv
                )
                
                logger.info(f"Import completed: {users_created} users and {memberships_created} memberships created")
                if errors:
                    logger.warning(f"Encountered {len(errors)} errors during import:")
                    for error in errors[:10]:  # Show first 10 errors
                        logger.warning(f"  {error}")
                    if len(errors) > 10:
                        logger.warning(f"  ... and {len(errors) - 10} more errors")

        except SQLAlchemyError as e:
            db.rollback()
            logger.error(f"Database error during initialization: {str(e)}")
        finally:
            db.close()
            
    except Exception as e:
        logger.error(f"Error initializing database: {str(e)}")
        raise

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Initialize the GSB Mandal database")
    parser.add_argument("--csv-import", action="store_true", help="Import member data from CSV files")
    parser.add_argument("--members-csv", help="Path to the members CSV file")
    parser.add_argument("--address-csv", help="Path to the address CSV file")
    
    args = parser.parse_args()
    
    # CSV import options
    csv_import = args.csv_import
    members_csv = args.members_csv
    address_csv = args.address_csv
    
    # Validate CSV import arguments
    if csv_import and (not members_csv or not address_csv):
        parser.error("--csv-import requires both --members-csv and --address-csv")
    
    init_db(csv_import, members_csv, address_csv)
    logger.info("Database initialization completed")