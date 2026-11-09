<?php
/**
 * วางเนื้อหาด้านล่างลงใน wp-config.php เหนือบรรทัด
 *   / * That's all, stop editing! Happy publishing. * /
 * (ไม่ต้องคัดลอกบรรทัด "<?php" ด้านบน)
 *
 * Salts: แทนที่บล็อก AUTH_KEY ... NONCE_SALT เดิมด้วยค่าใหม่จาก
 *   https://api.wordpress.org/secret-key/1.1/salt/
 * การเปลี่ยน salts ทำให้ทุก session หลุด (ใช้ได้เป็นมาตรการ incident response ด้วย)
 */

// ปิด theme/plugin editor ใน wp-admin (ถ้า admin ถูกยึด ก็แก้โค้ด PHP ผ่านหน้าเว็บไม่ได้)
define( 'DISALLOW_FILE_EDIT', true );

// ทางเลือกที่เข้มกว่า: ห้ามติดตั้ง/อัปเดต plugin/theme จากหน้า admin เลย
// เปิดเฉพาะเมื่อคุณอัปเดตผ่าน SFTP/hosting panel เป็นประจำ ไม่อย่างนั้นจะได้แพตช์ช้า
// define( 'DISALLOW_FILE_MODS', true );

// บังคับ HTTPS ในหน้า login และ admin
define( 'FORCE_SSL_ADMIN', true );

// ไม่แสดง error ให้ผู้เข้าชม (กันข้อมูล path/เวอร์ชันรั่ว)
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', false );
@ini_set( 'display_errors', '0' );

// อัปเดต core อัตโนมัติ (รวม major) — shared hosting ไม่มีทีมแพตช์ให้
define( 'WP_AUTO_UPDATE_CORE', true );

// ลด revision และล้างถังขยะเร็วขึ้น (DB เล็กลง, backup เร็วขึ้น)
define( 'WP_POST_REVISIONS', 10 );
define( 'EMPTY_TRASH_DAYS', 7 );

// ใช้ direct filesystem (กันหน้าถาม FTP credentials ระหว่างอัปเดต)
define( 'FS_METHOD', 'direct' );
