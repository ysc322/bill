#!/usr/bin/env python
# -*- coding:utf-8 -*-
#from selenium import webdriver
import time
from time import sleep # this should go at the top of the file
import os,base64 
import sys
#import logging
import requests
import json

g_fpdm = sys.argv[1]
g_fphm = sys.argv[2]
g_kprq = sys.argv[3]
g_kjje = sys.argv[4]

print(sys.argv)
exit()

from selenium.webdriver.chrome.options import Options
import selenium.webdriver.support.ui as ui
from selenium.webdriver.common.keys import Keys

# python /home/wwwroot/caiji/fapiao/fp3.py 061001900104 55074210 20200329 762145
# python /home/wwwroot/caiji/fapiao/fp3.py 2100191130 06952497 20191228 240592.49



chrome_options = Options()

chrome_options.add_argument('--no-sandbox') #让Chrome在root权限运行

chrome_options.add_argument('--disable-dev-shm-usage') #不打开图形界面

chrome_options.add_argument('--headless') #浏览器不提供可视化页面

chrome_options.add_argument('--ignore-ssl-errors=yes')
chrome_options.add_argument('--ignore-certificate-errors')
chrome_options.add_experimental_option('excludeSwitches', ['enable-automation'])
chrome_options.add_argument("--disable-blink-features")
chrome_options.add_argument("--disable-blink-features=AutomationControlled")
chrome_options.add_argument("---widows-size==2220,1500")

#chrome_options.add_argument('blink-settings=imagesEnabled=false') #不加载图片, 提升速度

chrome_options.add_argument('--disable-gpu') #谷歌文档提到需要加上这个属性来规避bug



driver = webdriver.Chrome(chrome_options=chrome_options,executable_path='/usr/local/bin/chromedriver') #Chrome驱动的位置，此学习记录中安装到了Chrome程序根目录，该路径为绝对路径


driver.execute_cdp_cmd("Page.addScriptToEvaluateOnNewDocument", {
  "source": """
    Object.defineProperty(navigator, 'webdriver', {
      get: () => undefined
    })
  """
})

#logging.basicConfig(level = logging.DEBUG)
driver.set_window_size(1200,1000)
driver.maximize_window()
url = 'https://inv-veri.chinatax.gov.cn'
#url = 'http://bill.ganbuguo.com/static/fp/fpiao.html';
driver.get(url) # 获取

js="var q=document.documentElement.scrollLeft=100000"#将滚动条移动到页面底部的js语句
driver.execute_script(js)#执行上面移动滚动条的js语句scrollLeft


html = driver.execute_script("return document.getElementsByTagName('html')[0].innerHTML")

inputss = driver.find_element_by_xpath('//*[@id="fpdm"]')#获取输入框
inputss.send_keys(g_fpdm + Keys.ENTER)#输入搜索关键词
inputs = driver.find_element_by_xpath('//*[@id="fphm"]')#获取输入框
inputs.send_keys(g_fphm + Keys.ENTER)#输入搜索关键词
kprq = driver.find_element_by_xpath('//*[@id="kprq"]')#获取输入框
kprq.send_keys(g_kprq + Keys.ENTER)#输入搜索关键词

driver.find_element_by_xpath('//*[@id="kprq"]').click()#点击搜索按钮

kjje = driver.find_element_by_xpath('//*[@id="kjje"]')#获取输入框
kjje.send_keys(g_kjje)#输入搜索关键词

time.sleep(1)#睡眠，等待异步加载的数据加载完成
src = driver.find_element_by_xpath("//td[@id='imgarea']/div/a/img").get_attribute("src")
src = src.replace('data:image/png;base64,','')
#print(src)
js="var q=document.documentElement.scrollTop=100000"#将滚动条移动到页面底部的js语句
driver.execute_script(js)#执行上面移动滚动条的js语句scrollLeft
driver.save_screenshot(r"/home/wwwroot/caiji/fapiao/jp.png")
#验证码颜色获取

yzminfo = driver.find_element_by_xpath('//*[@id="yzminfo"]').text
#请输入验证码图片中蓝色文字   蓝色
#请输入验证码图片中红色文字   红色
#请输入验证码图片中黄色文字   黄色
#请输入验证码文字

try:
	with open('/home/wwwroot/caiji/fapiao/test.png','wb') as f:
		f.write(base64.b64decode(src))


except:
	driver.quit()
	exit()


bs64 = src
need_color = ''
if yzminfo=='请输入验证码图片中蓝色文字':
	need_color = 'bule'

if yzminfo=='请输入验证码图片中黄色文字':
	need_color = 'yellow'
if yzminfo=='请输入验证码图片中红色文字':
	need_color = 'red'
if yzminfo=='请输入验证码文字':
	need_color = 'black'

#临时用接口测试
url = "http://39.100.22.138:19951/captcha/v1"
data = '{"image":"'+src+'","need_color":"'+need_color+'"}'
#字符串格式
res = requests.post(url=url,data=data)
#print(res.text)

json_to_python = json.loads(res.text)


#code = input(yzminfo+":")
code = ''
if json_to_python['code'] == 0:
	code = json_to_python['message']

yzm = driver.find_element_by_xpath('//*[@id="yzm"]')#获取输入框
yzm.send_keys(code + Keys.ENTER) #输入搜索关键词

value=yzm.get_attribute('value')
#print(value)

#time.sleep(1)#睡眠，等待异步加载的数据加载完成

popup_container = driver.find_element_by_id('checkfp').value_of_css_property('display')
#print(popup_container)

#driver.save_screenshot(r"/home/wwwroot/caiji/fapiao/jp1.png")
#driver.find_element_by_xpath('//*[@id="checkfp"]').click()#点击搜索按钮
a = driver.find_element_by_id('checkfp')
driver.execute_script("arguments[0].click();",a)

time.sleep(1)#睡眠，等待异步加载的数据加载完成

driver.save_screenshot(r"/home/wwwroot/caiji/fapiao/jp2.png")

#popup_container = driver.find_element_by_xpath('//*[@id="popup_message"]').text#获取输入框
#print(popup_container)

#切换到iframe 获取发票信息

try:
	iframe = driver.find_elements_by_tag_name("iframe")[0]
	driver.switch_to.frame(iframe)
except:
	print("{'code':1,'data':'iframe'}")
	driver.quit()
	exit()
try:
	driver.find_element_by_xpath('//*[@id="cycs"]')
	
except:
	print("{'code':1,'data':'cycs'}")
	driver.quit()
	exit()

try:
	driver.find_elements_by_class_name('content_td_blue')
except:
	print("{'code':1,'data':'content_td_blue'}")
	driver.quit()
	exit()
fall = driver.find_elements_by_class_name('content_td_blue')
infos = []
i=0
result = {}
for val in fall:
	try:
		result[val.get_attribute('id')] = val.text
	except:
		infos.append(val.text)
	i+=1

print("{'code':0,'data':'"+result+"'}")
#print(result)
#print(infos)

driver.quit()
exit()




cycs = driver.find_element_by_xpath('//*[@id="cycs"]').text #获取输入框 查验次数

lx = 'pp'
try:
	driver.find_element_by_xpath('//*[@id="fpdm_pp"]')
except:
	print('wfpdm_pp')
	lx = 'dzfp'

try:
	driver.find_element_by_xpath('//*[@id="fpdm_dzfp"]')
	lx = 'dzfp'
except:
	print('wfdzfp')
	lx = 'pp'
print(lx)
if lx == 'dzfp':
	fpdm_pp = driver.find_element_by_xpath('//*[@id="fpdm_dzfp"]').text #获取输入框 发票代码
	fphm_pp = driver.find_element_by_xpath('//*[@id="fphm_dzfp"]').text #获取输入框 发票号码
	fpcc_pp = driver.find_element_by_xpath('//*[@id="fpcc_dzfp"]').text #获取输入框 发票名称
	kprq_pp = driver.find_element_by_xpath('//*[@id="kprq_dzfp"]').text #获取输入框 发票开票日期
	jym_pp = driver.find_element_by_xpath('//*[@id="jym_dzfp"]').text #获取输入框   校验码
	jqbh_pp = driver.find_element_by_xpath('//*[@id="sbbh_dzfp"]').text #获取输入框 机器吗
	gfmc_pp = driver.find_element_by_xpath('//*[@id="gfmc_dzfp"]').text #获取输入框 购买方名称
	gfsbh_pp = driver.find_element_by_xpath('//*[@id="gfsbh_dzfp"]').text #获取输入框 纳税人识别号：
	gfdzdh_pp = driver.find_element_by_xpath('//*[@id="gfdzdh_dzfp"]').text #获取输入框 地址、 电话：
	gfyhzh_pp = driver.find_element_by_xpath('//*[@id="gfyhzh_dzfp"]').text #获取输入框 开户行及账号：


	jshjxx_pp = driver.find_element_by_xpath('//*[@id="jshjxx_dzfp"]').text #获取输入框  价税合计

	xfmc_pp = driver.find_element_by_xpath('//*[@id="xfmc_dzfp"]').text #获取输入框  销售方
	bz_pp = driver.find_element_by_xpath('//*[@id="bz_dzfp"]').text #获取输入框   备注
	xfsbh_pp = driver.find_element_by_xpath('//*[@id="xfsbh_dzfp"]').text #获取输入框 纳税人识别号：
	xfdzdh_pp = driver.find_element_by_xpath('//*[@id="xfdzdh_dzfp"]').text #获取输入框 地址、 电话：
	xfyhzh_pp = driver.find_element_by_xpath('//*[@id="xfyhzh_dzfp"]').text #获取输入框 开户行及账号：
if lx == 'pp':
	

	fpdm_pp = driver.find_element_by_xpath('//*[@id="fpdm_pp"]').text #获取输入框 发票代码
	fphm_pp = driver.find_element_by_xpath('//*[@id="fphm_pp"]').text #获取输入框 发票号码
	fpcc_pp = driver.find_element_by_xpath('//*[@id="fpcc_pp"]').text #获取输入框 发票名称
	kprq_pp = driver.find_element_by_xpath('//*[@id="kprq_pp"]').text #获取输入框 发票开票日期
	jym_pp = driver.find_element_by_xpath('//*[@id="jym_pp"]').text #获取输入框   校验码
	jqbh_pp = driver.find_element_by_xpath('//*[@id="jqbh_pp"]').text #获取输入框 机器吗
	gfmc_pp = driver.find_element_by_xpath('//*[@id="gfmc_pp"]').text #获取输入框 购买方名称
	gfsbh_pp = driver.find_element_by_xpath('//*[@id="gfsbh_pp"]').text #获取输入框 纳税人识别号：
	gfdzdh_pp = driver.find_element_by_xpath('//*[@id="gfdzdh_pp"]').text #获取输入框 地址、 电话：
	gfyhzh_pp = driver.find_element_by_xpath('//*[@id="gfyhzh_pp"]').text #获取输入框 开户行及账号：


	jshjxx_pp = driver.find_element_by_xpath('//*[@id="jshjxx_pp"]').text #获取输入框  价税合计

	xfmc_pp = driver.find_element_by_xpath('//*[@id="xfmc_pp"]').text #获取输入框  销售方
	bz_pp = driver.find_element_by_xpath('//*[@id="bz_pp"]').text #获取输入框   备注
	xfsbh_pp = driver.find_element_by_xpath('//*[@id="xfsbh_pp"]').text #获取输入框 纳税人识别号：
	xfdzdh_pp = driver.find_element_by_xpath('//*[@id="xfdzdh_pp"]').text #获取输入框 地址、 电话：
	xfyhzh_pp = driver.find_element_by_xpath('//*[@id="xfyhzh_pp"]').text #获取输入框 开户行及账号：


print(cycs)
print(fpdm_pp)
print(gfmc_pp)
print(gfyhzh_pp)
print(xfdzdh_pp)

driver.quit()
exit()
