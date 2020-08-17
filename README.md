# 2th Chat

2th Chat is a Thai group chatbox plugin for Discuz! X (CMS). There is no plan to support other language currently.

  - ต้นฉบับจากคุณ [newz](https://github.com/newz/2thchat)
  - ปัจจุบันพัฒนาโดย [popiazaza](https://github.com/popiazaza/2thchat)
  - กระทู้พัฒนาสามารถดูได้ที่ [ดิสคัสอินไทย](https://www.discuz.in.th/thread/5/1/1/)

# วิธีการติดตั้ง

1. ดาวน์โหลดและแตกไฟล์ zip ออกมา
2. อัปโหลดโฟลเดอร์ที่แตกออกมาไปยัง DISCUZ_ROOT (โฟลเดอร์หลัก Discuz! X)
3. **สำหรับเซิฟเวอร์ที่ไม่ได้ใช้ CHMOD 755 เป็นค่ามาตรฐานของโฟลเดอร์**

CHMOD 755 ไฟลเดอร์ source/plugin/th_chat/img_up

4. **สำหรับ Discuz! X3.4 ที่ต้องการใช้ Emoji ในแชท**

เปิดไฟล์ config/config_global.php
ค้นหา
```
$_config['db']['1']['dbcharset'] = 'utf8';
```

แล้วแก้เป็น
```
$_config['db']['1']['dbcharset'] = 'utf8mb4';
```

5. เข้าไปยัง **AdminCP** > **ปลั๊กอิน**
6. คลิกปุ่ม **ติดตั้ง** ข้างหลังปลั๊กอิน **Chat X Final**
7. คลิกปุ่ม **ใช้งาน** ข้างหลังปลั๊กอิน **Chat X Final**

# วิธีการอัปเกรด

**สำหรับคนที่ใช้เวอร์ชั่น 2.15 หรือก่อนหน้า ให้ทำตามขั้นตอนการติดตั้งในข้อ 4-5 ก่อน**
1. ดาวน์โหลดไฟล์ [plugin_th_chat_xxx.zip](https://github.com/popiazaza/2thchat/releases/) แล้วแตกไฟล์ zip ออกมา
2. อัปโหลดโฟลเดอร์ที่แตกออกมาไปยัง DISCUZ_ROOT (โฟลเดอร์หลัก Discuz! X)
3. เข้าไปยัง **AdminCP** > **ปลั๊กอิน**
4. คลิกปุ่ม **อัปเกรดปลั๊กอิน** ข้างหลังปลั๊กอิน **Chat X Final**


# วิธีติดตั้ง DIY addon (ไม่จำเป็น)

1. ดาวน์โหลดไฟล์ [addon_th_chat_diy.zip](https://github.com/popiazaza/2thchat/releases/) แล้วแตกไฟล์ zip ออกมา
2. อัปโหลดไฟล์ที่แตกออกมาไปยัง DISCUZ_ROOT (โฟลเดอร์หลัก Discuz! X)
3. เข้าไปยัง **AdminCP** > **เครื่องมือ**
4. ติ๊กถูกช่อง **แคชหมวดหมู่โมดูล DIY** แล้วกดปุ่ม **ตกลง** (ช่องอื่นปล่อยติ๊กไปแบบนั้น)
5. เมื่อแก้ไข DIY ให้เลือกโมดูล **โค้ด HTML** แล้วเลือก **แหล่งข้อมูล** เป็น **2th Chat**
