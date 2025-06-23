# Database Schema for G.S.B. Mandal, Thane Website

This document outlines the proposed database schema for the G.S.B. Mandal, Thane web application. The schema is designed for a relational database like PostgreSQL and is based on the data structure from the provided member data.

## Table of Contents

1.  [Users](#users)
2.  [Memberships](#memberships)
3.  [Pages](#pages)
4.  [Sevas](#sevas)
5.  [Bookings](#bookings)
6.  [Booking Items](#booking-items)
7.  [Entity Relationship Diagram (ERD) Overview](#entity-relationship-diagram-erd-overview)

---

### `users`

This is the central table for any individual interacting with the site, including members, non-members (guests), and administrators.

| Column          | Type      | Constraints                                  | Description                                           |
| --------------- | --------- | -------------------------------------------- | ----------------------------------------------------- |
| `id`            | `UUID`    | `PRIMARY KEY`, `DEFAULT gen_random_uuid()`   | Unique identifier for the user.                       |
| `first_name`    | `TEXT`    | `NOT NULL`                                   | User's first name.                                    |
| `middle_name`   | `TEXT`    |                                              | User's middle name.                                   |
| `surname`       | `TEXT`    | `NOT NULL`                                   | User's last name or surname.                          |
| `email`         | `TEXT`    | `NOT NULL`, `UNIQUE`                         | User's email address. Used for communication.         |
| `password_hash` | `TEXT`    |                                              | Hashed password for admin login. `NULL` for others.   |
| `mobile_no`     | `TEXT`    | `NOT NULL`                                   | User's 10-digit mobile number.                        |
| `user_type`     | `ENUM`    | `NOT NULL`, `DEFAULT 'NM'`                   | User type ('M' for Member, 'NM' for Non-Member).      |
| `is_admin`      | `BOOLEAN` | `NOT NULL`, `DEFAULT false`                  | Flag to indicate if the user is an administrator.     |
| `created_at`    | `TIMESTAMPTZ` | `NOT NULL`, `DEFAULT now()`                | Timestamp when the user record was created.           |
| `updated_at`    | `TIMESTAMPTZ` | `NOT NULL`, `DEFAULT now()`                | Timestamp when the user record was last updated.      |

---

### `memberships`

Stores detailed information for registered members, linked to the `users` table. This table is populated when a user with `user_type = 'M'` completes their profile.

| Column                  | Type      | Constraints                           | Description                                           |
| ----------------------- | --------- | ------------------------------------- | ----------------------------------------------------- |
| `id`                    | `UUID`    | `PRIMARY KEY`                         | Unique identifier for the membership record.          |
| `user_id`               | `UUID`    | `NOT NULL`, `UNIQUE`, `FOREIGN KEY (users.id)` | Links to the corresponding user.                      |
| `gender`                | `ENUM`    | `NOT NULL`                            | Gender (`MALE`, `FEMALE`).                            |
| `postal_address`        | `TEXT`    | `NOT NULL`                            | Full postal address.                                  |
| `pin_code`              | `TEXT`    | `NOT NULL`                            | 6-digit pin code.                                     |
| `date_of_birth`         | `DATE`    | `NOT NULL`                            | Member's date of birth.                               |
| `occupation`            | `TEXT`    | `NOT NULL`                            | Member's occupation.                                  |
| `qualification`         | `TEXT`    | `NOT NULL`                            | Member's educational qualification.                   |
| `marital_status`        | `ENUM`    | `NOT NULL`                            | Marital status (`MARRIED`, `UNMARRIED`).              |
| `number_of_kids`        | `INTEGER` |                                       | Number of children (if married).                      |
| `gotra`                 | `TEXT`    | `NOT NULL`                            | Member's Gotra.                                       |
| `kuladevata`            | `TEXT`    | `NOT NULL`                            | Member's Kuladevata.                                  |
| `math`                  | `ENUM`    | `NOT NULL`                            | Math affiliation (`KASHI`, `GOKARNA`, `KAVALE`).      |
| `native_place`          | `TEXT`    | `NOT NULL`                            | Member's native place.                                |
| `other_gsb_memberships` | `TEXT`    |                                       | Other GSB organizations the member is part of.        |
| `introducer_name`       | `TEXT`    |                                       | Name of the person who introduced the member.         |
| `membership_type`       | `ENUM`    | `NOT NULL`, `DEFAULT 'PATRON'`        | Type of membership (e.g., `PATRON`).                  |
| `status`                | `ENUM`    | `NOT NULL`, `DEFAULT 'PENDING'`       | Application status (`PENDING`, `APPROVED`, `REJECTED`).|
| `application_date`      | `TIMESTAMPTZ`| `NOT NULL`, `DEFAULT now()`         | Date of membership application.                       |
| `approval_date`         | `TIMESTAMPTZ`|                                    | Date of membership approval.                          |

---

### `pages`

Stores content for dynamic pages managed via the admin dashboard (e.g., About Us, announcements).

| Column          | Type      | Constraints                           | Description                                   |
| --------------- | --------- | ------------------------------------- | --------------------------------------------- |
| `id`            | `UUID`    | `PRIMARY KEY`                         | Unique identifier for the page.               |
| `title`         | `TEXT`    | `NOT NULL`                            | The title of the page.                        |
| `slug`          | `TEXT`    | `NOT NULL`, `UNIQUE`                  | URL-friendly slug for the page (e.g., `/about-us`). |
| `content`       | `TEXT`    | `NOT NULL`                            | Page content in Markdown or HTML format.      |
| `created_by`    | `UUID`    | `NOT NULL`, `FOREIGN KEY (users.id)`  | The admin user who created the page.          |
| `is_published`  | `BOOLEAN` | `NOT NULL`, `DEFAULT false`           | Whether the page is visible to the public.    |
| `created_at`    | `TIMESTAMPTZ`| `NOT NULL`, `DEFAULT now()`         | Timestamp of page creation.                   |
| `updated_at`    | `TIMESTAMPTZ`| `NOT NULL`, `DEFAULT now()`         | Timestamp of last update.                     |

---

### `sevas`

A master list of all sevas (poojas) offered by the Mandal, making them easy to manage.

| Column        | Type      | Constraints                 | Description                           |
| ------------- | --------- | --------------------------- | ------------------------------------- |
| `id`          | `UUID`    | `PRIMARY KEY`               | Unique identifier for the seva.       |
| `name`        | `TEXT`    | `NOT NULL`, `UNIQUE`        | The name of the seva (e.g., "Archana").|
| `description` | `TEXT`    |                             | A brief description of the seva.      |
| `price`       | `DECIMAL` | `NOT NULL`, `CHECK (price >= 0)` | The price of the seva.                |
| `is_active`   | `BOOLEAN` | `NOT NULL`, `DEFAULT true`  | Whether the seva is currently available for booking. |

---

### `bookings`

Represents a single transaction, which can include multiple sevas and/or a general donation.

| Column                | Type      | Constraints                           | Description                                           |
| --------------------- | --------- | ------------------------------------- | ----------------------------------------------------- |
| `id`                  | `UUID`    | `PRIMARY KEY`                         | Unique identifier for the booking.                    |
| `user_id`             | `UUID`    | `NOT NULL`, `FOREIGN KEY (users.id)`  | The user who made the booking.                        |
| `booking_date`        | `TIMESTAMPTZ`| `NOT NULL`, `DEFAULT now()`         | Timestamp when the booking was made.                  |
| `total_amount`        | `DECIMAL` | `NOT NULL`                            | The total amount paid in this transaction.            |
| `donation_amount`     | `DECIMAL` | `DEFAULT 0`                           | The portion of the total amount that is a general donation. |
| `pan_number`          | `TEXT`    |                                       | User's PAN, required for large donations.             |
| `payment_status`      | `ENUM`    | `NOT NULL`, `DEFAULT 'PENDING'`       | Status of the payment (`PENDING`, `COMPLETED`, `FAILED`). |
| `receipt_id`          | `TEXT`    | `UNIQUE`                              | A unique, human-readable ID for the generated receipt.|
| `payment_gateway_ref` | `TEXT`    |                                       | Reference ID from the payment gateway.                |
| `created_at`          | `TIMESTAMPTZ`| `NOT NULL`, `DEFAULT now()`         | Timestamp of booking record creation.                 |

---

### `booking_items`

A join table that links a booking to the specific sevas that were part of it.

| Column             | Type      | Constraints                           | Description                                           |
| ------------------ | --------- | ------------------------------------- | ----------------------------------------------------- |
| `id`               | `UUID`    | `PRIMARY KEY`                         | Unique identifier for the booking item.               |
| `booking_id`       | `UUID`    | `NOT NULL`, `FOREIGN KEY (bookings.id)` | Links to the overall booking transaction.             |
| `seva_id`          | `UUID`    | `NOT NULL`, `FOREIGN KEY (sevas.id)`    | Links to the specific seva that was booked.           |
| `quantity`         | `INTEGER` | `NOT NULL`, `DEFAULT 1`               | The quantity of this seva booked.                     |
| `price_at_booking` | `DECIMAL` | `NOT NULL`                            | The price of the seva at the time of booking.         |

---

## Entity Relationship Diagram (ERD) Overview

```
+-----------+       +---------------+       +-------------+
|   users   |----<--|  memberships  |       |    pages    |
|-----------|  (1)  |---------------|       |-------------|
| id (PK)   |       | id (PK)       |------>| created_by  | (FK to users.id)
| user_type |------>| user_id (FK)  |       | ...         |
| is_admin  | (1)   | ...           |       +-------------+
| ...       |       +---------------+
+-----------+
      |
      | (1)
      |
      | (makes)
      V
+-----------+       +------------------+       +---------+
| bookings  |----<--|  booking_items   |-->----|  sevas   |
|-----------|  (N)  |------------------|  (1)  |---------|
| id (PK)   |       | id (PK)          |       | id (PK) |
| user_id   |------>| booking_id (FK)  |       | name    |
| ...       |       | seva_id (FK)     |------>| price   |
+-----------+       | ...              |       | ...     |
                    +------------------+       +---------+
```

-   A `user` with `user_type = 'M'` can have one `membership`.
-   Any `user` can have many `bookings`.
-   A `booking` can have many `booking_items`.
-   Each `booking_item` corresponds to one `seva`.
-   A `user` with `is_admin = true` can create many `pages`.
