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


chaojiying_obj = Chaojiying_Client('���Լ�����Ϣ', '���Լ�����Ϣ', '���Լ�����Ϣ')

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
    '00': ['����', (0, 0, 0)],
    '01': ['��ɫ', (0, 0, 255)],
    '02': ['��ɫ', (0, 200, 200)],
    '03': ['��ɫ', (255, 0, 0)]
}


def generate_capture(color, rgb, capture_name, temp_capture_name, final_capture_name):
    """������֤��ͼƬ, ���˵������, ʹ����ϴ���ƽ̨Ҫ��"""

    # ����һ�ſհ�ͼƬ
    img = Image.new('RGB', (90, 20), (255, 255, 255))
    img.save(temp_capture_name)
    bk_img = cv2.imread(temp_capture_name)

    # ������Ҫ��ʾ������
    fontpath = "simsun.ttc"
    font = ImageFont.truetype(fontpath, 12)
    img_pil = Image.fromarray(bk_img)
    draw = ImageDraw.Draw(img_pil)

    # ����������Ϣ
    draw.text((0, 3),  "������", font=font, fill=(0, 0, 0))
    draw.text((38, 3),  color, font=font, fill=rgb)
    draw.text((65, 3),  "����", font=font, fill=(0, 0, 0))
    bk_img = np.array(img_pil)
    cv2.imwrite(temp_capture_name, bk_img)

    # �ϲ�ͼƬ
    photo_one = cv2.imread(temp_capture_name)
    photo_two = cv2.imread(capture_name)

    photo = np.vstack((photo_one, photo_two))
    cv2.imwrite(final_capture_name, photo)


timestamp = int(round(time.time() * 1000))
timestamp_pre = str((timestamp / 1000 - 60) * 1000).replace('.0', '')

# ��֤������
fpdm = '011001900311'  # ��Ʊ����
fphm = '26540678'  # ��Ʊ����
v = 'V2.0.04_004'  # �汾��
callback = 'jQuery110209376690644705499_{}'.format(timestamp_pre)  # �ȵ�ǰʱ���1����(1���ӿ������)
_ = str(int(timestamp_pre) + 1)  # ��¼��֤������Ĵ���(ÿ�μ�1, ���Թ̶�д��)
r = '0.' + ''.join(str(random.choice(range(10))) for _ in range(16))  # �����
nowtime = str(timestamp)
publickey = str(timestamp)
key9 = ctx1.call("key9_yzm", fpdm, fphm, nowtime)
flwq39 = ctx2.call("flwq39_yzm", fpdm, fphm, nowtime)

area = get_fpdm_area(fpdm)
if not area:
    print('��Ʊ�������')

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

# ������֤��ͼƬ, �����µ���֤��ͼƬ
generate_capture(color, rgb, capture_name, temp_capture_name, final_capture_name)

# ������֤�뵽����ƽ̨
with open(final_capture_name, 'rb') as f:
    capture_content = f.read()
code_dict = chaojiying_obj.PostPic(capture_content, 6004)
code = code_dict['pic_str']
print('��ȡ��֤��ɹ�:', code)

# ɾ����֤��ͼƬ
os.remove(capture_name)
os.remove(temp_capture_name)
os.remove(final_capture_name)

# ��ѯ����
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
    "key2": "6��20190708�Խ���Բ�ܵ����������޹�˾�����ֹ�˾��91110302585816506R�Ա����б������ü����������ٻ���·7��Ժ3��¥ʮ��1015 62648622�Խ��б�������֧�� 110060576018150114912�Ա�����ҵ��ѧ��1211000040086596XB�ԡԡ�78875685883799316342��0.00��69.49�ԡ�661620039941��69.49��0�ԡ�",
    "key3": "*ӡˢƷ*�����񻰣�40�������ļ���棩���ި���0.000��69.50000000��69.50��1.00000000��0.00��1��1060201019900000000��*ӡˢƷ*�����񻰣�40�������ļ���棩������0.000����-0.01����0.00��1��1060201019900000000",
    "key4": "������:99127168673",
    "key5": "1"
}
"""

# ��������
final_summarys = []
summarys = res_dict['key3'].split('��')
for index, summary in enumerate(summarys):
    summary_list = summary.split('��')
    summary_dict = dict()
    summary_dict['index'] = index + 1  # ���
    summary_dict['name'] = summary_list[0]  # ����
    summary_dict['type'] = summary_list[1]  # ����ͺ�
    summary_dict['unit'] = summary_list[2]  # ��λ
    summary_dict['amount'] = summary_list[6]  # ����
    summary_dict['priceUnit'] = summary_list[4]  # ����
    summary_dict['priceSum'] = summary_list[5]  # ���
    summary_dict['taxRate'] = '��˰'  # ˰��
    summary_dict['taxSum'] = '***'  # ˰��
    final_summarys.append(summary_dict)


key2_list = res_dict['key2'].split('��')

item = dict()
item['check_num'] = key2_list[10]  # У����
item['machine_num'] = key2_list[14]  # �������
item['sum_price'] = key2_list[15]  # �ϼƽ��
item['sum_tax'] = key2_list[16]  # �ϼ�˰��
item['order_num'] = res_dict['key4'].replace('������:', '')  # ������
item['buyer'] = {
    'name': key2_list[6],  # ����
    'taxpayer_identification_num': key2_list[7],  # ��˰��ʶ���
    'address_phone': key2_list[8],  # ��ַ���绰
    'bank_and_num': key2_list[9],  # �����м��˺�
}

item['seller'] = {
    'name': key2_list[2],  # ����
    'taxpayer_identification_num': key2_list[3],  # ��˰��ʶ���
    'address_phone': key2_list[4],  # ��ַ���绰
    'bank_and_num': key2_list[5],  # �����м��˺�
}

item['summarys'] = final_summarys  # ��������

print(item)
��������������������������������
��Ȩ����������ΪCSDN������black manba����ԭ�����£���ѭCC 4.0 BY-SA��ȨЭ�飬ת���븽��ԭ�ĳ������Ӽ���������
ԭ�����ӣ�https://blog.csdn.net/weixin_42156283/article/details/109211928