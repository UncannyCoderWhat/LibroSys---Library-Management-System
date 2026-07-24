# Functional Specification: Physical Book Borrowing and Return System

## 1. Overview

This document defines the functional requirements for the physical book borrowing and return subsystem of the LibroSys application. The system manages the complete lifecycle of physical book loans, including checkout, return, reservation queueing, and loan extensions.

---

## 2. Borrowing and Return Logic

### 2.1 Borrowing Workflow

1. **Eligibility Check**
   - Borrower must be authenticated.
   - Exclusive books require `credit_score > 5`.
   - Book must not be archived or deleted.

2. **Availability Check**
   - If `copies_available > 0` (no active `borrowed` records), the user may borrow immediately.
   - If all copies are borrowed and no reservations exist, the user may borrow immediately.
   - If all copies are borrowed and a reservation queue exists, only the first-in-line user may borrow.

3. **Loan Creation**
   - `borrow_date` = current timestamp
   - `due_date` = `borrow_date + 7 days`
   - `status` = `borrowed`
   - Delivery address is collected at checkout.

4. **Return Workflow**
   - Borrower initiates return of an active `borrowed` record.
   - System calculates fines if the return is late:
     - ≤3 days late: ₱50/day
     - 4–10 days late: ₱100/day
     - >10 days late: ₱150/day
   - `status` → `returned`, `return_date` = current timestamp
   - If reserved users are waiting in queue, the first in line is notified.
   - On-time returns improve credit score by +1; late returns penalize by -2.

---

## 3. Reservation and Queue Management

### 3.1 Reserve Button

- If a book is fully borrowed (`status = borrowed` for all copies), users see a **Reserve** button instead of **Borrow**.
- Reservation is not allowed if the user already has an active `borrowed` or `reserved` record for the same book.
- Reservation is not allowed if the book is currently available (`copies_available > 0`).

### 3.2 Queue Behavior

- Reservations are ordered by `borrow_date` (FIFO).
- When a borrowed copy is returned:
  - The system finds the oldest reservation (`ORDER BY borrow_date ASC LIMIT 1`).
  - A notification is inserted for that user: *"The book '<title>' you reserved is now available!"*
  - The reserving user may then complete checkout.

### 3.3 Queue Position Display

- On the book detail page and browse listings, users see their current queue position if reserved.
- Queue position = `COUNT(*)` of reservations with earlier `borrow_date` + 1.

---

## 4. Loan Extension Policy

### 4.1 Extension Rule

- A user may extend **once** per active borrowing period.
- Extension adds **7 days** to the current `due_date`.
- Extension is only permitted if the book is **not overdue**.
- Extension incurs a flat fee of **₱50**, added to `fine_amount`.
- After a successful extension, the borrow record is marked so further extensions are blocked.

### 4.2 Enforcement

- `extend_borrowing` checks for an `extension_used` flag (or equivalent).
- If `extension_used = 1`, the action is rejected with: *"You have already extended this loan."*
- On successful extension:
  - `due_date` = current `due_date + 7 days`
  - `extension_used` = `1`
  - `fine_amount` = `fine_amount + 50`

---

## 5. Database Schema Changes

### 5.1 `borrows` Table Additions

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| `extension_used` | TINYINT(1) | 0 | Whether the loan has already been extended |
| `queue_position` | INT NULL | NULL | Cached queue position for display purposes |

### 5.2 Migration SQL

```sql
ALTER TABLE borrows ADD COLUMN extension_used TINYINT(1) NOT NULL DEFAULT 0 AFTER fine_amount;
ALTER TABLE borrows ADD INDEX idx_extension (extension_used);
```

---

## 6. UI Requirements

### 6.1 Book Detail Actions

| User State | Primary Action | Secondary Action |
|------------|---------------|------------------|
| Available | Borrow | Bookmark |
| Booked / Reading | **Continue Reading** | — |
| Borrowed by others | Reserve | — |
| Already reserved | View Queue Position | Cancel Reservation |
| Active borrow | Return | Extend (if allowed) |

### 6.2 Notifications

- Notification model: *"The book '<title>' you reserved is now available!"*
- Shown in the user's notification panel.

---

## 7. Error Handling

| Scenario | Result |
|----------|--------|
| Borrow without available copies | Error: "This book is currently out on loan." |
| Reserve while book is available | Error: "This book is available on the shelves." |
| Reserve while already having active request | Error: "You already have an active request for this book." |
| Extend overdue book | Error: "This book is already overdue. Please return it first." |
| Extend twice | Error: "You have already extended this loan." |
| Return not found | Error: "Active borrow record not found." |

---

## 8. Credit Score Impact

| Action | Score Change |
|--------|-------------|
| On-time return | +1 |
| Late return | -2 |
| Extension fee paid | 0 |

Score is clamped to `[0, 10]`.
