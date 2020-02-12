#!/usr/bin/env python

# based on tutorials:
#   http://www.roman10.net/serial-port-communication-in-python/
#   http://www.brettdangerfield.com/post/raspberrypi_tempature_monitor_project/

import serial


def to_hex(text):
    ret = ""
    for ch in text:
        hv = "\\x" + format(ord(ch), "x")
        print(hv)
        ret += hv
    return ret


class MessageSender:
    SERIALPORT = "/dev/ttyUSB0"
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

    print("Starting Up Serial Monitor")

    __instance = None

    def __new__(cls):
        if MessageSender.__instance is None:
            MessageSender.__instance = object.__new__(cls)
        return MessageSender.__instance

    def __init__(self):

        try:
            if not self.ser.isOpen():
                self.ser.open()

        except Exception as e:
            print("error open serial port: " + str(e))
            exit()

    def __del__(self):
        self.ser.close()

    def send_message(self, text):
        print("Printing message: ", text)
        if self.ser.isOpen():

            try:
                self.ser.flushInput()  # flush input buffer, discarding all its contents
                self.ser.flushOutput()  # flush output buffer, aborting current output

                message = ("~128~f01C\\b\\g" + text + "\r\r\r").encode("UTF-8")

                self.ser.write(serial.to_bytes(message))

            except Exception as e:
                print("error communicating...: " + str(e))

        else:
            print("cannot open serial port ")
