# coding:utf-8
# 
import time
import execjs
import random
import requests
import urllib3
import re
import base64
import json
from datetime import datetime, timedelta

with open('./key9.js', encoding='utf-8') as f1:
    ctx1 = execjs.compile(f1.read(),cwd=r'/home/wwwroot/caiji/fapiao/html/node_modules')
    
key9 = ctx1.call('showurl','1100201130','18268336','94339.62')
print(key9)