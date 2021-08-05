# -*- coding: utf-8 -*-
import time
import execjs
import random
import requests
import urllib3
import re
import base64
import json
from datetime import datetime, timedelta
import cv2
from PIL import ImageFont, ImageDraw, Image
import numpy as np
import os
from get_area import get_fpdm_area
from chaojiying import Chaojiying_Client


chaojiying_obj = Chaojiying_Client('你自己的信息', '你自己的信息', '你自己的信息')

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

s = requests.session()
s.headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36'}

with open('./key9.js', encoding='utf-8') as f1:
    ctx1 = execjs.compile(f1.read())

with open('./flwq39.js', encoding='utf-8') as f2:
    ctx2 = execjs.compile(f2.read())

with open('./get_capture_data.js', encoding='utf-8') as f3:
    ctx3 = execjs.compile(f3.read())

with open('./fplx.js', encoding='utf-8') as f4:
    ctx4 = execjs.compile(f4.read())

capture_dict = {
    '00': ['所有', (0, 0, 0)],
    '01': ['红色', (0, 0, 255)],
    '02': ['黄色', (0, 200, 200)],
    '03': ['蓝色', (255, 0, 0)]
}


def generate_capture(color, rgb, capture_name, temp_capture_name, final_capture_name):
    """处理验证码图片, 添加说明文字, 使其符合打码平台要求"""

    # 生成一张空白图片
    img = Image.new('RGB', (90, 20), (255, 255, 255))
    img.save(temp_capture_name)
    bk_img = cv2.imread(temp_capture_name)

    # 设置需要显示的字体
    fontpath = "simsun.ttc"
    font = ImageFont.truetype(fontpath, 12)
    img_pil = Image.fromarray(bk_img)
    draw = ImageDraw.Draw(img_pil)

    # 绘制文字信息
    draw.text((0, 3),  "请输入", font=font, fill=(0, 0, 0))
    draw.text((38, 3),  color, font=font, fill=rgb)
    draw.text((65, 3),  "文字", font=font, fill=(0, 0, 0))
    bk_img = np.array(img_pil)
    cv2.imwrite(temp_capture_name, bk_img)

    # 合并图片
    photo_one = cv2.imread(temp_capture_name)
    photo_two = cv2.imread(capture_name)

    photo = np.vstack((photo_one, photo_two))
    cv2.imwrite(final_capture_name, photo)


timestamp = int(round(time.time() * 1000))
timestamp_pre = str((timestamp / 1000 - 60) * 1000).replace('.0', '')

# 验证码请求
fpdm = '011001900311'  # 发票代码
fphm = '26540678'  # 发票号码
v = 'V2.0.04_004'  # 版本号
callback = 'jQuery110209376690644705499_{}'.format(timestamp_pre)  # 比当前时间减1分钟(1分钟可以随机)
_ = str(int(timestamp_pre) + 1)  # 记录验证码请求的次数(每次加1, 可以固定写死)
r = '0.' + ''.join(str(random.choice(range(10))) for _ in range(16))  # 随机数
nowtime = str(timestamp)
publickey = str(timestamp)
key9 = ctx1.call("key9_yzm", fpdm, fphm, nowtime)
flwq39 = ctx2.call("flwq39_yzm", fpdm, fphm, nowtime)

area = get_fpdm_area(fpdm)
if not area:
    print('发票代码错误')

area_url = area[1]
capture_url = f'{area_url}/yzmQuery?' \
              f'callback={callback}&' \
              f'fpdm={fpdm}&fphm={fphm}&' \
              f'r={r}&' \
              f'v={v}&' \
              f'nowtime={nowtime}&' \
              f'publickey={publickey}&' \
              f'key9={key9}&' \
              f'_={_}&' \
              f'flwq39={flwq39}'

print(capture_url)
resp = s.get(url=capture_url, verify=False)

data = re.findall('data":"(.+?)"', resp.text)[0]
data = ctx3.call("replaceStr", data, nowtime)
data = base64.b64decode(data)
data_dict = json.loads(data.decode('utf-8'))
print(data_dict)

key1 = data_dict['key1']
image_data = base64.b64decode(key1)

random_num = ''.join(str(random.choice(range(10))) for _ in range(10))
capture_name = 'capture_{}.png'.format(random_num)
temp_capture_name = 'temp_capture_{}.png'.format(random_num)
final_capture_name = 'final_capture_{}.png'.format(random_num)

with open(capture_name, 'wb') as f:
    f.write(image_data)

capture_type = data_dict['key4']
capture_info = capture_dict[capture_type]
color = capture_info[0]
rgb = capture_info[1]

# 处理验证码图片, 生成新的验证码图片
generate_capture(color, rgb, capture_name, temp_capture_name, final_capture_name)

# 发送验证码到打码平台
with open(final_capture_name, 'rb') as f:
    capture_content = f.read()
code_dict = chaojiying_obj.PostPic(capture_content, 6004)
code = code_dict['pic_str']
print('获取验证码成功:', code)

# 删除验证码图片
os.remove(capture_name)
os.remove(temp_capture_name)
os.remove(final_capture_name)

# 查询请求
callback = 'jQuery1102030589417870189517_{}'.format(timestamp_pre)
key1 = '011001900311'
key2 = '26540678'
key3 = '20190708'
key4 = '316342'
fplx = ctx4.call("fplx", key1)
yzm = code
yzmSj = (datetime.utcnow() + timedelta(hours=8)).strftime("%Y-%m-%d %H:%M:%S")
index = data_dict['key3']
publickey = yzmSj
key9 = ctx1.call("key9_vat", fpdm, fphm, yzmSj)
_ = str(int(timestamp_pre) + 1)
flwq39 = ctx2.call("flwq39_vat", fpdm, fphm, yzmSj)

query_url = f'{area_url}/vatQuery?' \
            f'callback={callback}&' \
            f'key1={key1}&' \
            f'key2={key2}&' \
            f'key3={key3}&' \
            f'key4={key4}&' \
            f'fplx={fplx}&' \
            f'yzm={yzm}&' \
            f'yzmSj={yzmSj}&' \
            f'index={index}&' \
            f'publickey={publickey}&' \
            f'key9={key9}&' \
            f'_={_}&' \
            f'flwq39={flwq39}'

print(query_url)

resp = s.get(url=query_url, verify=False)
res_json = re.findall('\((.+?)\)', resp.text)[0]
res_dict = json.loads(res_json)
print(res_dict)

"""
{
    "key1": "001",
    "key2": "6≡20190708≡江苏圆周电子商务有限公司北京分公司≡91110302585816506R≡北京市北京经济技术开发区荣华中路7号院3号楼十层1015 62648622≡交行北京海淀支行 110060576018150114912≡北方工业大学≡1211000040086596XB≡≡≡78875685883799316342≡0.00≡69.49≡≡661620039941≡69.49≡0≡≡",
    "key3": "*印刷品*人月神话（40周年中文纪念版）█无██0.000█69.50000000█69.50█1.00000000█0.00█1█1060201019900000000≡*印刷品*人月神话（40周年中文纪念版）███0.000██-0.01██0.00█1█1060201019900000000",
    "key4": "订单号:99127168673",
    "key5": "1"
}
"""

# 解析数据
final_summarys = []
summarys = res_dict['key3'].split('≡')
for index, summary in enumerate(summarys):
    summary_list = summary.split('█')
    summary_dict = dict()
    summary_dict['index'] = index + 1  # 序号
    summary_dict['name'] = summary_list[0]  # 名称
    summary_dict['type'] = summary_list[1]  # 规格型号
    summary_dict['unit'] = summary_list[2]  # 单位
    summary_dict['amount'] = summary_list[6]  # 数量
    summary_dict['priceUnit'] = summary_list[4]  # 单价
    summary_dict['priceSum'] = summary_list[5]  # 金额
    summary_dict['taxRate'] = '免税'  # 税率
    summary_dict['taxSum'] = '***'  # 税额
    final_summarys.append(summary_dict)


key2_list = res_dict['key2'].split('≡')

item = dict()
item['check_num'] = key2_list[10]  # 校验码
item['machine_num'] = key2_list[14]  # 机器编号
item['sum_price'] = key2_list[15]  # 合计金额
item['sum_tax'] = key2_list[16]  # 合计税额
item['order_num'] = res_dict['key4'].replace('订单号:', '')  # 订单号
item['buyer'] = {
    'name': key2_list[6],  # 名称
    'taxpayer_identification_num': key2_list[7],  # 纳税人识别号
    'address_phone': key2_list[8],  # 地址、电话
    'bank_and_num': key2_list[9],  # 开户行及账号
}

item['seller'] = {
    'name': key2_list[2],  # 名称
    'taxpayer_identification_num': key2_list[3],  # 纳税人识别号
    'address_phone': key2_list[4],  # 地址、电话
    'bank_and_num': key2_list[5],  # 开户行及账号
}

item['summarys'] = final_summarys  # 具体事项

print(item)
————————————————
版权声明：本文为CSDN博主「black manba」的原创文章，遵循CC 4.0 BY-SA版权协议，转载请附上原文出处链接及本声明。
原文链接：https://blog.csdn.net/weixin_42156283/article/details/109211928