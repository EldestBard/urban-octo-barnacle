#include <SPI.h>
#include <Ethernet.h>
#define pin A0
char state = '0';
char c;
byte mac[] = {
  0x5E, 0x3A, 0x87, 0x57, 0xDD, 0x14};
EthernetClient client;
char server[] = "wxxcc.applinzi.com";  //改为自己的网址或IP地址
unsigned long lastConnectionTime = 0;
boolean lastConnected = false;
const unsigned long postingInterval = 200*1000;
unsigned long duration;
unsigned long starttime;
unsigned long sampletime_ms = 30000;
unsigned long lowpulseoccupancy = 0;
float ratio = 0;
float concentration = 0;
float pm25val = 0.05;
const float pm25coef = 0.00207916725464941;
//处理DSM501传回来的数据
float dsm501(void)
{
  duration = pulseIn(pin, LOW);
  lowpulseoccupancy = lowpulseoccupancy + duration;
  if ((millis() - starttime) > sampletime_ms)
  {
    ratio = lowpulseoccupancy / (sampletime_ms*10.0);  // Integer percentage 0=>100
    concentration = 1.1*pow(ratio, 3) - 3.8*pow(ratio, 2) + 520 * ratio + 0.62; // using spec sheet curve
    pm25val = pm25coef * concentration * 10;
    lowpulseoccupancy = 0;
    starttime = millis();
  }
  return pm25val;
}
//向服务器上传数据
void updata(float datapoint)
{
  if (!client.connected() && lastConnected) {
    //Serial.println("disconnecting.");
    client.stop();
  }
  if (!client.connected() && (millis() - lastConnectionTime > postingInterval)) {
    if (client.connect(server, 80)) {

      // send the HTTP PUT request:
      client.print("GET /downup.php?token=arduino&data=");
      client.print(datapoint);
      client.println(" HTTP/1.1");
      client.println("Host: wxxcc.applinzi.com");
      client.println("User-Agent: arduino-ethernet");
      client.println("Connection: close");
      client.println();
      lastConnectionTime = millis();
    }
    else {
      //Serial.println("connection failed");
      //Serial.println("disconnecting.");
      client.stop();
    }
  }
  lastConnected = client.connected();
}
//读取开关状态并做出反应
void switchmode(void)
{
  if (state == '0') {
    //state为0时的动作
  }
  else if (state == '1') {
    //state为1时的动作
  }
  while (client.available()) {
    c = client.read();
    if (c == '{') {
      state = client.read();
    }
  }
}
void setup() {
  Ethernet.begin(mac);
  pinMode(pin, INPUT);
  starttime = millis();
}

void loop(void) {
  switchmode();
  dsm501();
  updata(pm25val);
}
