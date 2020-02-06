#!/usr/bin/env python
import pika
import mysql.connector
import socket
import MessageSender
import json
import config

RABBIT_HOST = "some-rabbit"
EXCHANGE_NAME = "messages_exchange"
QUEUE_NAME = "messages_queue"
MYSQL_HOST = "185.8.239.18"
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

print(MY_DEVICE_NAME)

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
    msgsender = MessageSender.MessageSender()
    json_body = json.loads(body)
    print(body)
    print(json_body)
    print(json_body['message'])
    msgsender.send_message(json_body['message'])
    print(" [x] %r:%r" % (method.routing_key, body))


channel.basic_consume(
    queue=queue_name, on_message_callback=callback, auto_ack=True)

channel.start_consuming()

connection.close()

print("Goodbye, World!")
