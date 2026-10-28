# Student Clearance System — Qajeelfama Afaan Oromoo

## Install
1. ZIP kana extract godhi.
2. Folder `StudentClearance` gara `C:\\xampp\\htdocs\\StudentClearance` copy godhi.
3. XAMPP Control Panel bani; Apache fi MySQL Start godhi.
4. `http://localhost/phpmyadmin` bani, tab Import filadhu, `database/clearance_db.sql` filadhu, Go cuqaasi.
5. `http://localhost/StudentClearance/` bani.

**Of eeggannoo:** SQL file kun tables duraan jiran DROP godha; data duraan jiru ni bada. Backup godhi yoo data barbaachisaa qabda ta'e.

## Demo accounts
Password demo accounts hundaa: `password`

- Admin: `admin@campus.local`
- Library: `library@campus.local`
- Finance: `finance@campus.local`
- Department: `department@campus.local`
- Proctor Office: `proctor@campus.local`
- Cafe Office: `cafe@campus.local`
- Registrar Office: `registrar@campus.local`
- Student: `student@campus.local`

## Dashboard
- Admin dashboard
- Student dashboard
- Library dashboard
- Finance dashboard
- Department dashboard
- Proctor Office dashboard
- Cafe Office dashboard
- Registrar Office dashboard

Office accounts each see only their own office's requests. Student can track requests from all six offices.

Kun prototype barnootaati. Deployment dhugaa dura security review fi testing barbaada.

## Fooyya'iinsa design
- Student dashboard irratti campus hero banner, statistics cards, clearance status cards, fi help panel dabalamaniiru.
- Dashboard responsive dha; desktop fi mobile irratti layout jijjiirata.
- Campus banner: `assets/campus-banner.jpg`.
