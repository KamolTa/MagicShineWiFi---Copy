import paho.mqtt.client as mqtt
import urllib2

site = "Venezia"
host = "broker.hivemq.com"
port = 1883

def on_connect(self, client, userdata, rc):
    print("mqtt connected")
    self.subscribe("MagicShine/%s/Admin" % site)

def on_message(self, userdata, msg):
    message = msg.payload.decode("utf-8", "strict")
    x = message.split(",")

    if x[0] == "user_num":
	r = urllib2.urlopen("http://localhost/update_max_user.php?username=%s&user_num=%s" % (x[1],x[2]))
        self.publish("MagicShine/%s/Pi" % site,"Done with Message:%s" % message)

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message
client.connect(host)
client.loop_forever()
