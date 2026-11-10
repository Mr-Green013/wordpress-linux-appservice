# ร้านค้าออนไลน์ด้วย WooCommerce บน Shared Hosting (Hostinger / GoDaddy)

ทำต่อจาก [README.md](./README.md) (ติดตั้ง WordPress + hardening เสร็จแล้ว)

> ราคาและชื่อแพ็กเกจเปลี่ยนบ่อย ตัวเลขในเอกสารนี้เป็นค่าประมาณ ตรวจหน้าราคาจริงก่อนซื้อ

---

## 1. เลือกแพ็กเกจ hosting

ร้านค้าใช้ทรัพยากรมากกว่าเว็บทั่วไป (ตะกร้า/checkout แคชไม่ได้, มี cron, มีอีเมลออเดอร์)

| | ขั้นต่ำที่แนะนำ | เหตุผล |
|---|---|---|
| Hostinger | **Business** ขึ้นไป (หรือ Cloud Startup ถ้าคาดว่าออเดอร์เยอะ) | backup รายวัน, RAM/CPU มากกว่า, มี staging |
| GoDaddy | **Web Hosting (cPanel) Deluxe/Ultimate** — ไม่ใช่ Managed WordPress | แก้ `.htaccess`/`wp-config.php` ได้, ใช้ kit นี้ได้ครบ |

งบรวมโดยประมาณ (ปีแรก):

| รายการ | ราว/เดือน |
|---|---|
| Hosting (ราคาโปรเมื่อจ่ายล่วงหน้าหลายปี — ราคาต่ออายุสูงกว่า) | 100–400 บาท |
| WordPress + WooCommerce | ฟรี |
| ธีม (Storefront / Astra / Kadence เวอร์ชันฟรีใช้ได้) | 0–2,000 บาท/ปี ถ้าซื้อ Pro |
| Payment gateway | ไม่มีรายเดือน — ค่าธรรมเนียมต่อรายการ (ดูข้อ 4) |
| Backup offsite (Google Drive ฟรี / S3) | 0–50 บาท |

---

## 2. ติดตั้ง WooCommerce

1. wp-admin → Plugins → Add New → **WooCommerce** (ของ Automattic เท่านั้น) → Activate
2. Setup wizard:
   - Store address: ที่อยู่จริงในไทย, Currency: **Thai Baht (฿)**
   - ข้ามการติดตั้งปลั๊กอินแนะนำ/marketing ที่ไม่ใช้ (Jetpack, MailPoet ฯลฯ) — ติดเฉพาะที่จำเป็น
3. WooCommerce → Settings
   - **General:** ประเทศขาย = Thailand (หรือประเทศที่ส่งจริง), ปิด "Enable coupons" ถ้าไม่ใช้
   - **Products:** หน่วยน้ำหนัก kg / ขนาด cm, เปิด manage stock
   - **Tax:** ถ้าจด VAT — เปิด tax, ตั้ง 7%, เลือกแสดงราคารวม VAT
   - **Accounts & Privacy:**
     - เปิด guest checkout (ลดการสร้างบัญชีที่ไม่จำเป็น = ลดข้อมูลที่ต้องดูแล)
     - ปิด "Allow customers to create an account on the My account page" ถ้าไม่ต้องการระบบสมาชิก
     - ตั้ง **personal data retention** (เช่น ลบออเดอร์ที่ยกเลิก/ล้มเหลวหลัง 6 เดือน) — สอดคล้อง PDPA
     - ลิงก์หน้า Privacy Policy และ Terms
   - **Emails:** ตั้ง From address เป็นอีเมลโดเมนตัวเอง (ต้องมี SPF/DKIM ไม่อย่างนั้นอีเมลออเดอร์เข้า spam)
   - **Advanced → Features:** ใช้ **High-Performance Order Storage (HPOS)** และ checkout แบบ block

### หน้าที่ต้องมี
หน้าแรก, ร้านค้า, ตะกร้า, ชำระเงิน, บัญชีของฉัน (WooCommerce สร้างให้) และเขียนเพิ่ม:
- นโยบายความเป็นส่วนตัว (PDPA), เงื่อนไขการใช้บริการ
- นโยบายการคืนสินค้า/คืนเงิน, การจัดส่ง
- ติดต่อเรา (ชื่อร้าน/บริษัท, ที่อยู่, เบอร์โทร, เลขผู้เสียภาษีถ้ามี)
- ถ้าเป็นผู้ค้าออนไลน์ในไทย ตรวจเงื่อนไข **จดทะเบียนพาณิชย์อิเล็กทรอนิกส์** กับกรมพัฒนาธุรกิจการค้า

---

## 3. ปลั๊กอินที่ใช้ (ยิ่งน้อยยิ่งปลอดภัย)

| หน้าที่ | ปลั๊กอิน | หมายเหตุ |
|---|---|---|
| ร้านค้า | WooCommerce | |
| ชำระเงิน | gateway ไทย 1 เจ้า (ข้อ 4) | |
| ความปลอดภัย | Wordfence หรือ Solid Security | มีอยู่แล้วจาก README |
| Backup | UpdraftPlus | **backup ฐานข้อมูลถี่ขึ้น** (ทุก 4–12 ชม.) เพราะออเดอร์อยู่ใน DB |
| กันบอท checkout | Cloudflare Turnstile หรือ reCAPTCHA สำหรับ WooCommerce | กัน card testing / สมัครสมาชิกปลอม |
| SMTP | WP Mail SMTP (ผ่าน SMTP ของ hosting หรือ Brevo/SES) | อีเมลออเดอร์ถึงลูกค้าจริง |
| Cookie consent | Complianz / CookieYes / ปลั๊กอิน PDPA ไทย | จำเป็นถ้าใช้ GA/Facebook Pixel |
| Cache | LiteSpeed Cache (Hostinger) | WooCommerce ตั้ง exclude cart/checkout/my-account ให้อัตโนมัติ — ตรวจอีกครั้ง |

ไม่แนะนำ: ปลั๊กอิน nulled, page builder หลายตัวซ้อนกัน, ปลั๊กอินที่ไม่อัปเดตเกิน 12 เดือน

---

## 4. รับชำระเงิน (ตลาดไทย)

เลือก gateway ที่มีปลั๊กอิน WooCommerce ทางการ และรองรับ **PromptPay QR + บัตร + mobile banking**
ตัวอย่างที่นิยม (ตรวจค่าธรรมเนียมล่าสุดกับผู้ให้บริการ):

- **Opn Payments (เดิม Omise)** — ปลั๊กอิน WooCommerce ทางการ, บัตร/PromptPay/TrueMoney/mobile banking
- **2C2P** — รองรับหลายช่องทาง/หลายประเทศ
- **GB Prime Pay** — ผู้ให้บริการไทย, มี PromptPay QR
- **โอนเงิน + แนบสลิป** (Direct bank transfer ในตัว WooCommerce) — ไม่มีค่าธรรมเนียม แต่ต้องตรวจสลิปเองและเสี่ยงสลิปปลอม

**กฎความปลอดภัยการชำระเงิน**
- ใช้แบบ **redirect / hosted payment page / hosted fields** ที่ข้อมูลบัตรไม่ผ่านเซิร์ฟเวอร์เรา → PCI DSS ระดับ SAQ A (ภาระต่ำสุด)
- ห้ามเก็บเลขบัตรเองไม่ว่ากรณีใด
- ตั้ง webhook secret ตามคู่มือ gateway และยืนยันยอดเงินจาก webhook/API ไม่ใช่จาก redirect URL ฝั่งลูกค้า
- ทดสอบใน **test mode** ครบทุกช่องทาง (สำเร็จ / ล้มเหลว / ยกเลิก / คืนเงิน) ก่อนเปิดจริง
- ถ้ารับสลิป: ตรวจยอดกับ statement/แอปธนาคารทุกครั้ง หรือใช้บริการตรวจสลิปอัตโนมัติ

---

## 5. การจัดส่ง

- WooCommerce → Settings → Shipping → สร้าง **Shipping zone "Thailand"**
  - Flat rate (เช่น 50 บาท) + Free shipping เมื่อซื้อครบ X บาท
  - ถ้าค่าส่งต่างตามน้ำหนัก ใช้ปลั๊กอิน table rate
- เชื่อมขนส่ง (Flash, Kerry/KEX, ไปรษณีย์ไทย, J&T) ผ่านบริการรวมขนส่งที่มีปลั๊กอิน WooCommerce
  เพื่อพิมพ์ใบปะหน้าและส่งเลขพัสดุให้ลูกค้าอัตโนมัติ — เลือกเจ้าที่ปลั๊กอินยังอัปเดตอยู่
- ฟอร์มที่อยู่ไทย: ปลั๊กอิน autocomplete จังหวัด/อำเภอ/ตำบล/รหัสไปรษณีย์ช่วยลดที่อยู่ผิด

---

## 6. Hardening เพิ่มเติมสำหรับร้านค้า

นอกจาก kit ใน README (wp-config, `.htaccess`, mu-plugin) ซึ่งใช้กับ WooCommerce ได้ตามเดิม:

| ภัย | มาตรการ |
|---|---|
| **Card testing** (บอทลองบัตรผ่าน checkout) | Turnstile/reCAPTCHA ที่ checkout, เปิด fraud/velocity rules ของ gateway, rate limit ใน Wordfence |
| **Web skimmer / Magecart** (JS ขโมยข้อมูลหน้า checkout) | ใช้ hosted fields, ปลั๊กอินน้อยที่สุด, Wordfence file-change scan, ห้ามใส่ script ภายนอกที่ไม่จำเป็นในหน้า checkout |
| **Admin ถูกยึด** | 2FA ทุกบัญชีที่มี role Shop Manager/Admin, แยกบัญชีพนักงาน (Shop Manager ไม่ใช่ Administrator) |
| **WooCommerce REST API key รั่ว** | สร้าง key เฉพาะที่จำเป็น, สิทธิ์ Read ถ้าพอ, ลบ key ที่ไม่ใช้ (WooCommerce → Settings → Advanced → REST API) |
| **ข้อมูลลูกค้า (PDPA)** | เก็บเท่าที่จำเป็น, ตั้ง retention, จำกัดคนเข้าถึงออเดอร์, มีขั้นตอนแจ้งเหตุข้อมูลรั่วภายใน 72 ชม. |
| **ปลั๊กอินมีช่องโหว่** | เปิด auto-update ปลั๊กอินที่เชื่อถือได้, ทดสอบ major update บน staging (Hostinger มีให้) ก่อน |
| **Log รั่ว** | WooCommerce เก็บ log ที่ `wp-content/uploads/wc-logs/` — กฎ `*.log` ใน `htaccess-root.example` บล็อกให้แล้ว |

mu-plugin `security-hardening.php` ไม่กระทบ WooCommerce: REST ของร้าน (`/wc/v3`, `/wc/store`) ยังทำงาน
และ WooCommerce API key ไม่ได้ใช้ Application Passwords

---

## 7. ตรวจก่อนเปิดร้าน

```bash
D=https://yourdomain.com
# หน้า checkout/cart ต้องไม่ถูกแคช
curl -sI $D/cart/ | grep -Ei 'cache-control|x-litespeed-cache'
# REST ของ WooCommerce ต้องไม่เปิดข้อมูลโดยไม่มี key
curl -s -o /dev/null -w '%{http_code}\n' $D/wp-json/wc/v3/orders      # คาดว่า 401
curl -s -o /dev/null -w '%{http_code}\n' $D/wp-json/wc/v3/customers   # คาดว่า 401
# log ต้องเข้าถึงไม่ได้
curl -s -o /dev/null -w '%{http_code}\n' $D/wp-content/uploads/wc-logs/  # คาดว่า 403
```

- [ ] สั่งซื้อทดสอบครบทุกช่องทางชำระเงิน (test mode) + คืนเงิน 1 รายการ
- [ ] อีเมลยืนยันออเดอร์ถึงลูกค้าและร้าน (ไม่เข้า spam)
- [ ] ค่าส่ง/VAT/คูปองคำนวณถูก
- [ ] หน้า Privacy / Terms / Refund / Shipping / Contact ครบ
- [ ] Cookie consent ทำงานก่อนโหลด tracking script
- [ ] 2FA ทุกบัญชี admin / shop manager
- [ ] Backup DB อัตโนมัติ + ทดลอง restore แล้ว
- [ ] สลับ gateway เป็น live mode แล้วซื้อจริง 1 รายการเล็กๆ
