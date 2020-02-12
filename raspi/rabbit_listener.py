#!/usr/bin/env python
import pika
import mysql.connector
import socket
import MessageSender
import json
import config
import sys
from datetime import datetime

MSG_TYPE_NORMAL = "normal"
MSG_TYPE_RELOAD = "reload"

RABBIT_HOST = "185.8.239.18"
EXCHANGE_NAME = "messages_exchange"
QUEUE_NAME = "messages_queue"
MYSQL_HOST = "185.8.239.18"
if len(sys.argv) > 1 and sys.argv[1]:
    MY_DEVICE_NAME = (sys.argv[1])
else:
    MY_DEVICE_NAME = socket.gethostname()

mydb = mysql.connector.connect(
    host=MYSQL_HOST,
    user=config.mysql_user,
    passwd=config.mysql_password,
    port=3369,
    database="fyp",
    use_pure=True,
    ssl_disabled=True
)

mycursor = mydb.cursor()

connection = pika.BlockingConnection(pika.ConnectionParameters(RABBIT_HOST))
channel = connection.channel()
channel.exchange_declare(exchange=EXCHANGE_NAME, exchange_type='topic', durable=True)
result = channel.queue_declare(queue='', durable=False)
queue_name = result.method.queue

print("Device: " + MY_DEVICE_NAME)

mycursor.execute(
    "SELECT q.id, q.name FROM queue q LEFT JOIN device_in_queue diq ON diq.queue_id = q.id LEFT JOIN device d ON d.id = diq.device_id WHERE d.name = %s GROUP BY q.id",
    (MY_DEVICE_NAME,))

myresult = mycursor.fetchall()
print("My queues:")
print(myresult)
if myresult:
    for x in myresult:
        channel.queue_bind(
            exchange=EXCHANGE_NAME, queue=queue_name, routing_key="#." + x[1] + ".#")
else:
    print("No queues to bind to")


def callback(ch, method, properties, body):
    print(" [x] Received %r" % body)


print(' [*] Waiting for logs. To exit press CTRL+C')


def callback(ch, method, properties, body):
    json_body = json.loads(body)
    now = datetime.now()
    current_time = now.strftime("%H:%M")
    message = current_time + " " + json_body["message"]
    msg_type = json_body["type"]
    if msg_type == MSG_TYPE_RELOAD:
        # exit the program. Docker will restart it and reload the new configuration
        print("Received reload message, exiting now")
        exit()
    elif msg_type == MSG_TYPE_NORMAL:
        msgsender = MessageSender.MessageSender()
        if "ttl" in json_body:
            ttl = json_body["ttl"]
        else:
            ttl = config.default_message_ttl
        msgsender.send_message(text=message, ttl=ttl)
        # print(" [x] %r:%r" % (method.routing_key, body))
    else:
        print("Unknown message type")


channel.basic_consume(
    queue=queue_name, on_message_callback=callback, auto_ack=True)

channel.start_consuming()

connection.close()

print("Goodbye, World!")
