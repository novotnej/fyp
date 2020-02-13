#!/usr/bin/env python

# based on tutorials:
#   http://www.roman10.net/serial-port-communication-in-python/
#   http://www.brettdangerfield.com/post/raspberrypi_tempature_monitor_project/

import serial
import calendar
import time
import threading
import config


def to_hex(text):
    ret = ""
    for ch in text:
        hv = "\\x" + format(ord(ch), "x")
        print(hv)
        ret += hv
    return ret


class MessageSender:
    SERIALPORT = config.SERIALPORT
    BAUDRATE = 9600

    ser = serial.Serial()
    ser.port = SERIALPORT
    ser.baudrate = BAUDRATE
    ser.bytesize = serial.EIGHTBITS  # number of bits per bytes
    ser.parity = serial.PARITY_NONE  # set parity check: no parity
    ser.stopbits = serial.STOPBITS_ONE  # number of stop bits
    # ser.timeout = None          #block read
    # ser.timeout = 0             #non-block read
    ser.timeout = 2  # timeout block read
    ser.xonxoff = False  # disable software flow control
    ser.rtscts = False  # disable hardware (RTS/CTS) flow control
    ser.dsrdtr = False  # disable hardware (DSR/DTR) flow control
    ser.writeTimeout = 0  # timeout for write

    __messages = []
    __last_text = ""
    __timer_thread = None

    print("Starting Up Serial Monitor")

    __instance = None

    def __new__(cls):
        if MessageSender.__instance is None:
            MessageSender.__instance = object.__new__(cls)
        return MessageSender.__instance

    def __init__(self):
        try:
            if not self.ser.isOpen():
                if config.SERIALPORT:
                    self.ser.open()
                threading.Timer(10, self.check_expired_messages).start()

        except Exception as e:
            print("error open serial port: " + str(e))
            exit()

    def __del__(self):
        self.ser.close()

    def check_expired_messages(self):
        print("Checking expired messages")

        text = self.get_current_display_text()
        if text != self.__last_text:
            self.draw_text(text)
            self.__last_text = text

        threading.Timer(10, self.check_expired_messages).start()

    def draw_text(self, text):
        if self.ser.isOpen():
            try:
                self.ser.flushInput()  # flush input buffer, discarding all its contents
                self.ser.flushOutput()  # flush output buffer, aborting current output

                message = ("~128~f01C\\b\\g" + text + "\r\r\r").encode("UTF-8")

                self.ser.write(serial.to_bytes(message))

            except Exception as e:
                print("error communicating...: " + str(e))

        else:
            print("serial port is not open")

    def get_current_display_text(self):
        text = ""
        ts = calendar.timegm(time.gmtime())
        for msg in self.__messages:
            if ts < msg["expiration"]:
                text = text + "  \n" + msg["message"]
            else:
                self.__messages.remove(msg)
        return text

    def send_message(self, text, ttl):
        print("Printing message: ", text)
        ts = calendar.timegm(time.gmtime())
        expiration = ts + ttl
        self.__messages.append({"message": text, "expiration": expiration})
        self.__last_text = self.get_current_display_text()
        self.draw_text(self.__last_text)
