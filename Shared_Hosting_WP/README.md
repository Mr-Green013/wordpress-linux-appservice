# WordPress บน Shared Hosting (Hostinger / GoDaddy) — คู่มือติดตั้ง + Hardening

โฟลเดอร์นี้สำหรับกรณีที่ **โดเมนและ hosting อยู่เจ้าเดียวกัน** (Hostinger หรือ GoDaddy)
ไม่ใช้ Azure template ในส่วนอื่นของ repo นี้

```
Shared_Hosting_WP/
├── README.md                              # คู่มือนี้
├── wp-config-hardening.php                # ค่าที่ต้องเพิ่มใน wp-config.php
├── htaccess-root.example                  # security rules สำหรับ /public_html/.htaccess
├── htaccess-uploads.example               # กันรัน PHP ใน wp-content/uploads
└── mu-plugins/security-hardening.php      # ปิด XML-RPC, user enumeration ฯลฯ
```

> **หมายเหตุ:** แผน *Managed WordPress* ของ GoDaddy ไม่ให้แก้ `.htaccess` / `wp-config.php` ได้อิสระ
> ถ้าจะใช้ kit นี้ครบ ให้เลือกแผน **Web Hosting (cPanel)** ของ GoDaddy หรือแผนใดก็ได้ของ Hostinger (hPanel)

---

## 1. ติดตั้ง WordPress

### Hostinger (hPanel)
1. hPanel → **Websites** → **Add website** → **WordPress**
2. เลือกโดเมนที่จดไว้ → ตั้ง **username ของ admin ที่ไม่ใช่ `admin`** และรหัสผ่านยาว ≥ 16 ตัว (เก็บใน password manager)
3. ใช้อีเมลที่ไม่เปิดเผยต่อสาธารณะเป็นอีเมล admin
4. รอติดตั้งเสร็จ → **Security → SSL** ต้องขึ้น *Active* (Hostinger ออก SSL ให้ฟรีอัตโนมัติ)

### GoDaddy (cPanel Web Hosting)
1. My Products → Web Hosting → **Manage** → **cPanel Admin**
2. **Installatron/WordPress** → Install → เลือกโดเมน, ใช้ `https://` และ **ไม่ใส่ `www`** (หรือใส่ให้ตรงกับที่ต้องการใช้จริง)
3. ใน Advanced: เปลี่ยน **table prefix** จาก `wp_` เป็นค่าสุ่ม เช่น `w7k_`, ปิด "auto-install plugins" ที่ไม่ต้องการ
4. **SSL**: ตรวจที่ cPanel → *SSL/TLS Status* ว่าโดเมนได้ cert แล้ว
   ถ้าแผนไม่มี SSL ฟรี → ใช้ Cloudflare (Free) เป็น DNS/proxy แล้วตั้ง SSL mode = **Full (strict)**

### DNS
โดเมนจดเจ้าเดียวกับ hosting จึงชี้ให้อัตโนมัติ ตรวจได้ด้วย
```bash
dig +short yourdomain.com A
dig +short www.yourdomain.com
curl -sI https://yourdomain.com | head -5
```

---

## 2. Hardening (ทำทันทีหลังติดตั้ง)

ใช้ **File Manager** ใน hPanel/cPanel (หรือ SFTP — **อย่าใช้ FTP ธรรมดา**) ที่โฟลเดอร์ `public_html/`

| ขั้น | ไฟล์ | ทำอะไร |
|---|---|---|
| 1 | `wp-config.php` | วางเนื้อหาจาก `wp-config-hardening.php` **เหนือ**บรรทัด `/* That's all, stop editing! */` และเปลี่ยน salts ใหม่ |
| 2 | `public_html/.htaccess` | วางเนื้อหาจาก `htaccess-root.example` **เหนือ** `# BEGIN WordPress` (อย่าแก้ในบล็อก WordPress เพราะจะถูกเขียนทับ) |
| 3 | `wp-content/uploads/.htaccess` | สร้างไฟล์ใหม่จาก `htaccess-uploads.example` |
| 4 | `wp-content/mu-plugins/security-hardening.php` | สร้างโฟลเดอร์ `mu-plugins` แล้วอัปโหลดไฟล์ — โหลดอัตโนมัติ ปิดจากหน้า admin ไม่ได้ |
| 5 | permission | ไฟล์ `644`, โฟลเดอร์ `755`, `wp-config.php` = `440` หรือ `400` (ถ้า host รองรับ) |

หลังทำแต่ละขั้น **เปิดเว็บและ `/wp-admin` ทดสอบทันที** ถ้าเจอ 500 error ให้ย้อนไฟล์ที่เพิ่งแก้

### ปลั๊กอินที่แนะนำ (ให้น้อยที่สุด)
- **Wordfence** (หรือ Solid Security) — WAF ระดับแอป, malware scan, จำกัดการ login, **2FA ให้ทุกบัญชี admin**
- **UpdraftPlus** — backup อัตโนมัติไป Google Drive/OneDrive/S3 (*นอก* hosting) ทุกวัน, เก็บ ≥ 14 ชุด
- ลบ theme/plugin ที่ไม่ได้ใช้ทั้งหมด (รวม Hello Dolly และ theme default ที่เหลือ เก็บไว้ 1 ตัวสำรอง)
- ติดตั้งจาก wordpress.org เท่านั้น — **ห้ามใช้ปลั๊กอิน/ธีม nulled**

### ตั้งค่าใน WordPress
- Settings → General: ทั้ง *WordPress Address* และ *Site Address* เป็น `https://`
- Settings → Discussion: ถ้าไม่ใช้คอมเมนต์ → ปิด, ถ้าใช้ → เปิด moderation + Akismet/Antispam Bee
- Users: แต่ละคนมีบัญชีของตัวเอง, ให้ role ต่ำสุดที่พอใช้ (Editor/Author), ไม่แชร์บัญชี admin
- เปิด auto-update ให้ plugins/themes ที่เชื่อถือได้

### ฝั่ง hosting
- เปิด **2FA บัญชี Hostinger/GoDaddy** — ใครเข้าบัญชีนี้ได้ คุมได้ทั้งโดเมน, DNS และเว็บ
- เปิด **Domain lock / Transfer lock** และ WHOIS privacy
- PHP version: เลือก **8.3 หรือ 8.4** (hPanel → Advanced → PHP Configuration / cPanel → Select PHP Version)
- ปิด FTP accounts ที่ไม่ใช้, ใช้ SFTP/SSH key
- ตั้ง email record: SPF, DKIM, DMARC (`p=quarantine` เมื่อมั่นใจ) เพื่อกันคนปลอมอีเมลโดเมนคุณ

---

## 3. ตรวจสอบหลังเสร็จ

```bash
D=https://yourdomain.com
curl -sI $D | grep -Ei 'strict-transport|x-content-type|x-frame|referrer-policy|permissions-policy'
curl -s -o /dev/null -w '%{http_code}\n' $D/xmlrpc.php          # คาดว่า 403
curl -s -o /dev/null -w '%{http_code}\n' "$D/?author=1"          # คาดว่า 403
curl -s -o /dev/null -w '%{http_code}\n' $D/wp-json/wp/v2/users  # คาดว่า 401
curl -s -o /dev/null -w '%{http_code}\n' $D/wp-config.php.bak    # คาดว่า 403 หรือ 404
curl -s -o /dev/null -w '%{http_code}\n' http://yourdomain.com   # คาดว่า 301 ไป https
```
แล้วสแกนภายนอก: <https://securityheaders.com>, <https://www.ssllabs.com/ssltest/>
และ WPScan (`wpscan --url $D --enumerate vp,vt,u`) — สแกนเฉพาะเว็บของคุณเอง

---

## 4. Checklist

- [ ] SSL active, http → https redirect ทำงาน
- [ ] admin username ไม่ใช่ `admin`, รหัสผ่านยาว, เปิด 2FA
- [ ] 2FA บัญชี hosting + domain transfer lock
- [ ] `wp-config.php` hardening + salts ใหม่
- [ ] `.htaccess` root + uploads
- [ ] mu-plugin security-hardening
- [ ] Wordfence + UpdraftPlus (offsite backup) — ทดลอง **restore** อย่างน้อย 1 ครั้ง
- [ ] ลบ plugin/theme ที่ไม่ใช้, เปิด auto-update
- [ ] PHP 8.3+
- [ ] SPF / DKIM / DMARC
- [ ] ผล curl ตรวจสอบในข้อ 3 ผ่านทั้งหมด

## ข้อควรระวัง
- **Jetpack** และแอป WordPress บนมือถือต้องใช้ XML-RPC — ถ้าใช้ ให้เอากฎ `xmlrpc.php` ออกจาก `.htaccess` และ filter ใน mu-plugin
- `Strict-Transport-Security` เปิดใช้เมื่อ HTTPS ใช้ได้ทั้งโดเมนแล้วเท่านั้น (เริ่ม `max-age=300` ก่อนค่อยเพิ่ม)
- ถ้า Hostinger เปิด LiteSpeed Cache ไว้ ให้ purge cache หลังแก้ `.htaccess`
