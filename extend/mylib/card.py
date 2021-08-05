import cv2
import numpy as np
import matplotlib.image as mpimg
import matplotlib.pyplot as plt
import imutils
# 定义银行卡的卡号位置，主要使用opencv对卡号进行处理

'''
银行卡长宽85.60毫米、53.98毫米，长宽比0.623
首先判断银行卡的长宽比是否在0.60-0.690之间
如果在：
        reshape（550，350）模拟比例0.623
如果不在：
    高斯模糊->灰度化->边缘检测->二值化->找轮廓->轮廓判断
这一部分是银行卡的图像处理部分
'''


class Image:
    HEIGHT = 550
    WIDTH = 350
    img = 0
    remove_back_img = 0
    number_area = 0

    def __init__(self, img):
        self.img = img
        H, W, _ = img.shape
        print(img.shape)
        #图片旋转
        '''
        if H  > W :
            rows,cols = img.shape[:2]
            M = cv2.getRotationMatrix2D((cols / 2, rows / 2), 90, 0.5)
            self.img = cv2.warpAffine(self.img, M, (cols, rows))
'''


        if H / W > 0.6 and H / W < 0.69:
            # 如果在这个区间，图片reshape(550.350)
            self.remove_back_img = cv2.resize(self.img, (self.HEIGHT, self.WIDTH))
        else:
            # 如果不在，处理iamge的背景
            self.remove_back_img = self.removeBackground()
            # 计算银行卡数字的位置
        self.number_area = self.position(self.remove_back_img)
        # 获取银行卡号码
        self.pos_img = self.getNumberArea()


    def getNumberArea(self):
        num_img = self.number_area
        h, w, _ = num_img.shape
        # 灰度化处理
        gray_img = cv2.cvtColor(num_img, cv2.COLOR_BGR2GRAY)
        # 浮雕化处理
        dilate_img = self.embossment(gray_img)
        # 中值滤波
        embo_img = cv2.medianBlur(dilate_img, 3)

        # 二值化处理
        _, thresh_img = cv2.threshold(embo_img, 155, 255, cv2.THRESH_BINARY)

        # 中值滤波，高斯滤波，侵蚀
        thresh_img = cv2.medianBlur(thresh_img, 3)

        thresh_img = cv2.GaussianBlur(thresh_img, (3, 3), 0)

        thresh_img = cv2.dilate(thresh_img, None, iterations=10)

        # 二值化处理
        _, thresh_img = cv2.threshold(thresh_img, 220, 255, cv2.THRESH_BINARY)

        a = np.zeros(w, np.uint8)
        for j in range(0, w):  # 计算水平方向上的黑色像素点数目，
            for i in range(0, h):
                if thresh_img[i, j] == 0:
                    a[j] += 1

        a = a[::-1]
        length = int(0.75 * w)
        min_ = sum(a)
        start = 0
        for i in range(len(a)):
            if a[i] < 15:
                a[i] = 0
            else:
                a[i] = 35
        for i in range(w - length):
            end = i + length
            mean_ = a[i:end].mean()
            if (min_ > mean_ and i < 50):
                min_ = mean_
                start = i
        end = w - start
        a = a[::-1]
        min_ = sum(a)
        start = 0
        for i in range(130):
            mean_ = a[i:end].mean()
            if min_ > mean_:
                min_ = mean_
                start = i
        # print(start,end)
        self.W_end = end + 20
        self.W_start = start + 25

        #cv2.imshow('aaa', num_img[:, start:end + 10])
        #cv2.waitKeyEx()
        return num_img[:, start + 5:end]

    def removeBackground(self):
        # 先将图像处理成550，350的大小区间
        resize_img = cv2.resize(self.img, (self.HEIGHT, self.WIDTH), 0, 0, cv2.INTER_NEAREST)  # 调整图片大小
        self.img = resize_img
        # print(resize_img.shape)
        # cv2.imshow('resize_img', resize_img)

        '''
        灰度->中值滤波->Sobel边缘检测->二值处理->去除多余部分的背景。
        '''
        gray_img = cv2.cvtColor(resize_img, cv2.COLOR_BGR2GRAY)# 灰度处理
        cv2.imwrite('200.jpg', gray_img)
        blur_img = cv2.medianBlur(gray_img, 5)  # 中值滤波去除噪声# Sobel边缘检测
        cv2.imwrite('2.jpg', blur_img)
        x = cv2.Sobel(blur_img, cv2.CV_32F, 1, 0, 3)  #x线条
        y = cv2.Sobel(blur_img, cv2.CV_32F, 0, 1, 3)  #y线条
        absX = cv2.convertScaleAbs(x)					#转成8ut
        absY = cv2.convertScaleAbs(y)
        sobel_img = cv2.addWeighted(absX, 0.5, absY, 0.5, 0) #合并两个方向的值
        cv2.imwrite('3.jpg', sobel_img)


        # 自适应二值化
        thresh_img = cv2.adaptiveThreshold(sobel_img, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 3, 0)
       
        cv2.imwrite('5.jpg', thresh_img)
       # 检测轮廓，找到连通区域，也就是银行卡号区域
        cnts, _ = cv2.findContours(thresh_img, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)


        '''
                    img是一个二值图，boundingRect返回四个值，
                    分别是x，y，w，h；
                    x，y是矩阵左上点的坐标
                    w，h是矩阵的宽和高
                    判断图像的宽高是否都在
                    如果在：
                            reshape(x+w,y+h)
                            对面目标图像像素:(x, y)其值等于原图像(x * w_ration, y * h_ration)处的值
                    返回图像
                    '''

        temp = 0
        W = 0
        H = 0
        X = 0
        Y = 0
        for i in range(0, len(cnts)):
            x, y, w, h = cv2.boundingRect(cnts[i])

            if ( h>100):
                print(h)
                print(555555555)
            if (temp < w + h):

                print(x, y, w, h)
                temp = w + h 
                W = w
                H = h
                X = x
                Y = y
        print(X, Y, W, H)
        remove_back_img = resize_img[Y:Y + H, X:X + W]
        cv2.imwrite('4.jpg', remove_back_img)
        return cv2.resize(remove_back_img, (self.HEIGHT, self.WIDTH), cv2.INTER_NEAREST)

    # 图片浮雕处理
    '''	
    根据像素与周围像素的差值确定像素值，差别较大的像素（边缘点通常像素差别较大）像素值较大，
    在灰度图中表现为较亮，边缘凸显，形成浮雕状，然后加上一个灰度偏移值，作为图片的整体底色。
    实现公式：newP = gray0-gray1+150
    '''

    def embossment(self, img):
        H, W = img.shape
        dst = np.zeros((H, W), np.uint8)
        for i in range(0, H):
            for j in range(0, W - 1):
                grayP0 = int(img[i, j])
                grayP1 = int(img[i, j + 1])
                newP = grayP0 - grayP1 + 150
                if newP > 255:
                    newP = 255
                if newP < 0:
                    newP = 0
                dst[i, j] = newP
        return dst


    '''
    图像水平方向的黑色像素进行统计
    剩下黑色像素少的区域就是所要找的银行卡卡号区域
    '''

    def horizontal(self, img):
        H, W = img.shape
        # test_img = np.ones((H,W)) * 255
        hor_array = np.zeros(H, np.int32)
        for j in range(0, H):
            for i in range(0, W):
                if img[j, i] == 0:
                    hor_array[j] += 1
        return hor_array

    def getArea(self, array):
        '''
        除去顶部，计算平均值小的区域就是银行卡卡号位置所在
        选取图片高的1/10为一个区间用于计算不同区域的平均值。
        '''
        H = len(array)
        label_H = int(H / 10)
        min_ = sum(array)
        ans = 0
        for i in range(int(1 / 2 * H) - label_H):  # 从图像高2/5位置处开始进行平均值计算。
            a = int(2 / 5 * H) + i
            b = int(2 / 5 * H) + i + label_H
            mean = array[a:b].mean()
            if mean < min_:
                ans = a
                min_ = mean
            if a > 0.6 * H:
                return ans, ans + label_H

    def position(self, img):
        '''灰度->膨胀->腐蚀'''
        gray_img = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        gray_img = cv2.dilate(gray_img, None, iterations=2)
        gray_img = cv2.erode(gray_img, None, iterations=2)
        # 浮雕化处理,突出图像的边缘部分
        emboss_img = self.embossment(gray_img)
        sobel_x = cv2.Sobel(emboss_img, cv2.CV_32F, 1, 0, 3)  # 边缘检测
        sobel_y = cv2.Sobel(emboss_img, cv2.CV_32F, 0, 1, 3)
        absX = cv2.convertScaleAbs(sobel_x)
        absY = cv2.convertScaleAbs(sobel_y)
        sobel_img = cv2.addWeighted(absX, 0.5, absY, 0.5, 0)
        sobel_img = cv2.medianBlur(sobel_img, 11)  # 中值模糊
        sobel_img = cv2.dilate(sobel_img, None, iterations=2)  # 膨胀
        _, threshold = cv2.threshold(sobel_img, 10, 255, cv2.THRESH_BINARY)  # 二值化
        threshold = cv2.GaussianBlur(threshold, (9, 9), 0)  # 高斯模糊

        # pixel_array,test_img = self.horizontal(threshold)
        pixel_array = self.horizontal(threshold)  # 对图形黑色像素进行竖直投影
        start, end = self.getArea(pixel_array)
        self.H_start = start
        self.H_end = end
        res = img[start:end, 20:]
       # cv2.imshow('res', res)
        return res

lena = mpimg.imread('19.jpg')
A1 = Image(lena)