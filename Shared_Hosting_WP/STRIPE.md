# รับชำระเงินด้วย Stripe บน WooCommerce (Shared Hosting)

ทำต่อจาก [WOOCOMMERCE.md](./WOOCOMMERCE.md)

> ค่าธรรมเนียมและช่องทางที่เปิดให้ร้านไทยเปลี่ยนได้ — ตรวจที่ <https://stripe.com/th/pricing>
> และใน Stripe Dashboard → Settings → Payment methods ก่อนตัดสินใจ

---

## 0. ข้อควรรู้ก่อนเลือก Stripe

| หัวข้อ | สถานะ |
|---|---|
| ร้าน/บริษัทในไทยสมัครได้ | ได้ (Stripe เปิดในไทยแล้ว) ต้องมีเอกสารยืนยันตัวตน/ธุรกิจ และบัญชีธนาคารไทยสำหรับรับเงิน |
| บัตร Visa / Mastercard / ฯลฯ | ได้ |
| Apple Pay / Google Pay | ได้ผ่านปลั๊กอิน (ต้องยืนยันโดเมน — ปลั๊กอินทำให้) |
| **PromptPay** | Stripe รองรับในไทย **แต่ปลั๊กอิน WooCommerce Stripe ทางการอาจยังไม่มีตัวเลือกนี้** — ตรวจในหน้า settings ของปลั๊กอินเวอร์ชันที่ติดตั้ง ถ้าไม่มี ให้ใช้ gateway ไทย (เช่น Opn) สำหรับ PromptPay คู่กับ Stripe สำหรับบัตร |
| PCI DSS | ใช้ Payment Element (iframe ของ Stripe) → ข้อมูลบัตรไม่ผ่านเซิร์ฟเวอร์เรา → SAQ A |

---

## 1. สมัครและตั้งค่าบัญชี Stripe

1. สมัครที่ stripe.com เลือกประเทศ **Thailand** ประเภทบัญชีให้ตรงกับธุรกิจจริง (บุคคลธรรมดา / นิติบุคคล)
2. กรอกข้อมูลธุรกิจ, เว็บไซต์ (โดเมนร้าน), บัญชีธนาคารรับเงิน, ยืนยันตัวตน
3. **Security ของบัญชี Stripe (ทำก่อนเปิดรับเงิน)**
   - เปิด **2FA** (แนะนำ security key / authenticator app, ไม่ใช้ SMS ถ้าเลี่ยงได้)
   - Settings → Team: เพิ่มพนักงานเป็นบัญชีแยก ใช้ role ต่ำสุด (เช่น Support / View only) ห้ามแชร์บัญชี owner
   - เปิดอีเมลแจ้งเตือนเมื่อ **เปลี่ยนบัญชีธนาคารรับเงิน / มีการ login ใหม่** — การเปลี่ยน payout account เป็นเป้าหมายหลักของผู้โจมตี
   - ตั้ง statement descriptor ให้ลูกค้าจำได้ (ลด chargeback "ไม่รู้จักรายการนี้")

---

## 2. ติดตั้งปลั๊กอิน

1. wp-admin → Plugins → Add New → **WooCommerce Stripe Payment Gateway** (ผู้พัฒนา: WooCommerce) → Activate
   - ใช้ตัวทางการตัวเดียว ไม่ติดปลั๊กอิน Stripe หลายตัวซ้อนกัน
2. WooCommerce → Settings → Payments → Stripe → **Connect with Stripe** (OAuth)
   - ทำทั้ง **Test mode** และ **Live mode**
   - ถ้าต้องใส่ API key เอง: ใช้ key จาก Dashboard → Developers → API keys
     **secret key (`sk_live_…`) ห้ามส่งทางแชต/อีเมล ห้าม commit ลง git** — เก็บใน password manager
3. เปิด payment methods ที่ต้องการ (Card, Apple Pay/Google Pay) และใช้ checkout แบบใหม่ (Payment Element)
4. ตั้ง **Capture: ทันที** (หรือ authorize อย่างเดียวถ้าต้องตรวจสต็อก/ออเดอร์ก่อน — authorize หมดอายุภายในไม่กี่วัน)

---

## 3. Webhook (สำคัญ — สถานะออเดอร์อาศัยตัวนี้)

ปลั๊กอินเวอร์ชันใหม่ตั้งให้อัตโนมัติหลัง Connect — ตรวจว่าหน้า settings ขึ้น **"Enabled"** สีเขียว
ถ้าต้องตั้งเอง:

1. Stripe Dashboard → Developers → Webhooks → Add endpoint
2. URL: `https://yourdomain.com/?wc-api=wc_stripe`
3. Events: ตามที่หน้า settings ของปลั๊กอินระบุ (อย่างน้อย `payment_intent.succeeded`, `payment_intent.payment_failed`,
   `charge.refunded`, `charge.dispute.created`)
4. คัดลอก **Signing secret (`whsec_…`)** ไปใส่ในหน้า settings ปลั๊กอิน — ปลั๊กอินใช้ตรวจลายเซ็นทุก request
5. กด Send test webhook → ต้องได้ 200

**ต้องไม่บล็อก webhook:**
- `.htaccess` และ mu-plugin ใน kit นี้ไม่บล็อก `?wc-api=wc_stripe`
- Wordfence: ถ้า webhook โดน rate limit/บล็อก ให้ allowlist IP ของ Stripe (รายการที่ <https://stripe.com/files/ips/ips_webhooks.txt>) แทนการปิด firewall
- LiteSpeed/Cloudflare cache: ห้ามแคช URL ที่มี `wc-api` (ปกติ POST ไม่ถูกแคชอยู่แล้ว)

---

## 4. ทดสอบใน Test mode

| สถานการณ์ | บัตรทดสอบ (วันหมดอายุอนาคตใดก็ได้, CVC 3 หลักใดก็ได้) |
|---|---|
| สำเร็จ | `4242 4242 4242 4242` |
| ต้องยืนยัน 3D Secure | `4000 0025 0000 3155` |
| ถูกปฏิเสธ (เงินไม่พอ) | `4000 0000 0000 9995` |
| ถูกปฏิเสธ (ทั่วไป) | `4000 0000 0000 0002` |

ตรวจให้ครบ:
- [ ] ออเดอร์เปลี่ยนเป็น *Processing* หลังจ่ายสำเร็จ (มาจาก webhook)
- [ ] จ่ายไม่ผ่าน → ออเดอร์ *Failed/Pending* และลูกค้าลองใหม่ได้
- [ ] 3D Secure popup ทำงานบนมือถือ
- [ ] คืนเงินจาก wp-admin (Order → Refund via Stripe) แล้ว Stripe Dashboard ตรงกัน
- [ ] คืนเงินจาก Stripe Dashboard แล้วออเดอร์ใน WooCommerce อัปเดตตาม (ทดสอบ webhook ขาเข้า)

---

## 5. กันการโกง (Stripe Radar)

Card testing คือภัยที่พบบ่อยที่สุดกับร้าน WooCommerce + Stripe

- Dashboard → Radar → Rules: เปิด/เพิ่มกฎ
  - Block ถ้า **CVC check fails**
  - Request 3D Secure ถ้า **3DS supported** หรือ risk level elevated
  - Block ถ้า risk level **highest**
  - (ถ้าขายในไทยอย่างเดียว) Review ถ้าประเทศของบัตร ≠ TH
- ฝั่งเว็บ: ใส่ Cloudflare Turnstile / reCAPTCHA ที่ checkout (ดู WOOCOMMERCE.md ข้อ 3)
- ตั้ง alert ใน Stripe เมื่อมี **dispute** และตอบ dispute ภายในกำหนดพร้อมหลักฐานการจัดส่ง

---

## 6. เปิดใช้จริง

1. ปลั๊กอิน → ปิด Test mode → ตรวจว่า live webhook ขึ้น Enabled
2. ซื้อจริง 1 รายการราคาต่ำด้วยบัตรตัวเอง → คืนเงิน → ตรวจทั้งสองฝั่ง
3. ตรวจ payout schedule ใน Stripe (เงินเข้าบัญชีธนาคารกี่วัน)
4. บันทึกขั้นตอน incident ไว้: ถ้าสงสัยว่า secret key รั่ว → Dashboard → Developers → API keys → **Roll key** แล้ว Connect ปลั๊กอินใหม่, เปลี่ยน WordPress salts, ตรวจ Team members และบัญชีรับเงิน
