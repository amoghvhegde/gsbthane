import csv
import os
import pandas as pd
from datetime import datetime
from sqlalchemy.orm import Session
from uuid import UUID, uuid4
from typing import Dict, List, Optional, Tuple

from app import models, schemas
from app.models.user import UserType
from app.models.membership import Gender, MaritalStatus, Math, MembershipType, MembershipStatus

def parse_date(date_str: str) -> Optional[datetime]:
    """Parse date string into datetime object."""
    if not date_str or date_str == "":
        return None
    
    formats = [
        "%m/%d/%Y",  # MM/DD/YYYY
        "%d/%m/%Y",  # DD/MM/YYYY
        "%Y-%m-%d",  # YYYY-MM-DD
    ]
    
    for fmt in formats:
        try:
            return datetime.strptime(date_str, fmt)
        except ValueError:
            continue
    
    return None

def import_members_from_csv(
    db: Session,
    members_csv_path: str,
    address_csv_path: str
) -> Tuple[int, int, List[str]]:
    """
    Import member data from CSV files.
    
    Args:
        db: Database session
        members_csv_path: Path to the members CSV file
        address_csv_path: Path to the address CSV file
        
    Returns:
        Tuple containing (users_created, memberships_created, errors)
    """
    # Load address data into a dictionary for lookup
    address_data = {}
    with open(address_csv_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            addr_code = row.get('ADDR CODE', '')
            if addr_code:
                address_data[addr_code] = row
    
    # Process member data
    users_created = 0
    memberships_created = 0
    errors = []
    
    with open(members_csv_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        
        for row in reader:
            try:
                # Extract user data
                member_code = row.get('MEMBER CODE', '')
                if not member_code:
                    errors.append(f"Missing MEMBER CODE in row")
                    continue
                
                # Generate email if not available
                addr_code = row.get('ADDR CODE', '')
                email = None
                mobile = None
                
                if addr_code in address_data:
                    addr = address_data[addr_code]
                    # Try to get email (multiple columns may have it)
                    for email_col in ['EMAIL-1', 'EMAIL-2', 'EMAIL-3', 'EMAIL-4']:
                        if email_col in addr and addr[email_col] and addr[email_col] != "#N/A":
                            email = addr[email_col]
                            break
                    
                    # Try to get mobile (multiple columns may have it)
                    for mobile_col in ['MOBILE', 'MOBILE 1', 'MOBILE 2', 'MOBILE 3', 'MOBILE 4']:
                        if mobile_col in addr and addr[mobile_col] and addr[mobile_col] != "#N/A":
                            # Clean mobile number
                            mobile_raw = addr[mobile_col]
                            # Extract first numeric part that's 10 digits
                            import re
                            mobile_match = re.search(r'(\d{10})', mobile_raw.replace(',', '').replace(' ', ''))
                            if mobile_match:
                                mobile = mobile_match.group(1)
                                break
                
                # Skip if we can't find an email or mobile
                if not email and not mobile:
                    errors.append(f"No valid email or mobile for member {member_code}")
                    continue
                
                # Create a unique but deterministic email for users without one
                if not email:
                    email = f"member{member_code}@placeholder.gsb"
                
                # Create user
                first_name = row.get('FIRST NAME', '')
                middle_name = row.get('MIDDLE NAME', '')
                surname = row.get('SURNAME', '')
                
                if not first_name or not surname:
                    errors.append(f"Missing name information for member {member_code}")
                    continue
                
                # Check if user already exists
                existing_user = db.query(models.User).filter(models.User.email == email).first()
                if existing_user:
                    errors.append(f"User with email {email} already exists")
                    continue
                
                user = models.User(
                    first_name=first_name,
                    middle_name=middle_name,
                    surname=surname,
                    email=email,
                    mobile_no=mobile if mobile else "0000000000",  # Placeholder if no mobile
                    user_type=UserType.MEMBER,
                    is_admin=False
                )
                db.add(user)
                db.flush()  # To get the user.id
                users_created += 1
                
                # Create membership
                gender_str = row.get('GENDER', '')
                gender = Gender.MALE if gender_str == 'MALE' else Gender.FEMALE
                
                dob_str = row.get('DATE OF BIRTH', '')
                dob = parse_date(dob_str)
                
                # Get address details
                postal_address = ""
                pin_code = "400000"  # Default for Mumbai
                
                if addr_code in address_data:
                    addr = address_data[addr_code]
                    address_parts = []
                    
                    # Compile address from parts
                    for field in ['BLDG NAME', 'WING & FLAT NO', 'DETAILED ADDRESS', 'LOCATION']:
                        if field in addr and addr[field] and addr[field] != "#N/A":
                            address_parts.append(addr[field].strip())
                    
                    postal_address = ", ".join(address_parts)
                    
                    if 'PINCODE' in addr and addr['PINCODE'] and addr['PINCODE'] != "#N/A":
                        pin_code = addr['PINCODE']
                
                # Create membership with available data
                membership = models.Membership(
                    user_id=user.id,
                    gender=gender,
                    postal_address=postal_address if postal_address else "Address not provided",
                    pin_code=pin_code,
                    date_of_birth=dob if dob else datetime(1900, 1, 1),  # Default date
                    occupation=row.get('OCCUPATION', '') or "Not provided",
                    qualification=row.get('QUALIFICATION', '') or "Not provided",
                    marital_status=MaritalStatus.MARRIED,  # Default
                    gotra=row.get('GOTRA', '') or "Not provided",
                    kuladevata=row.get('KULDEVTHA', '') or "Not provided",
                    math=Math.KASHI,  # Default
                    native_place=row.get('NATIVE PLACE', '') or "Not provided",
                    introducer_name=row.get('INTRODUCER NAME', ''),
                    membership_type=MembershipType.PATRON,
                    status=MembershipStatus.APPROVED,
                    application_date=parse_date(row.get('DATE OF JOINING', '')) or datetime.now(),
                    approval_date=parse_date(row.get('DATE OF JOINING', '')) or datetime.now(),
                )
                db.add(membership)
                memberships_created += 1
                
            except Exception as e:
                errors.append(f"Error processing member {row.get('MEMBER CODE', 'unknown')}: {str(e)}")
    
    # Commit changes to database
    db.commit()
    
    return users_created, memberships_created, errors