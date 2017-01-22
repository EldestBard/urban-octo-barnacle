#include <SPI.h>
#include <Ethernet.h>
#define pin A0
char state = '0';
char c;
byte mac[] = { 
  0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED};
//IPAddress ip(192,168,1,49);
 
//IPAddress myDns(192,168,1,254);

EthernetClient client;
char server[] = "wxxcc.applinzi.com";
//int sensordata = 0;
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
void setup(){
  pinMode(LED_BUILTIN,OUTPUT);
  // 设置串口通信波特率
  //Serial.begin(9600);
  delay(1000);
  Ethernet.begin(mac);
  //Serial.print("My IP address: ");
  //Serial.println(Ethernet.localIP());
  pinMode(pin,INPUT);
  starttime = millis();
}
  
void loop(void){
  //sensordata = analogRead(pin)/4;
  if(state == '0'){
    digitalWrite(LED_BUILTIN, LOW);      
  }else if(state == '1'){
    digitalWrite(LED_BUILTIN, HIGH);
  }
 
  while(client.available()) {
    c = client.read();
    if (c == '{'){
      state = client.read();
    }
  }
  duration = pulseIn(pin, LOW);
  lowpulseoccupancy = lowpulseoccupancy+duration;

  if ((millis()-starttime) > sampletime_ms)
  {
    ratio = lowpulseoccupancy/(sampletime_ms*10.0);  // Integer percentage 0=>100
    concentration = 1.1*pow(ratio,3)-3.8*pow(ratio,2)+520*ratio+0.62; // using spec sheet curve
    pm25val = pm25coef * concentration * 10;
    lowpulseoccupancy = 0;
    starttime = millis();
  }
  if (!client.connected() && lastConnected) {
    //Serial.println("disconnecting.");
    client.stop();
  }
 
  if(!client.connected() && (millis() - lastConnectionTime > postingInterval)) {
    if (client.connect(server, 80)) {
 
      // send the HTTP PUT request:
      client.print("GET /downup.php?token=arduino&data=");
      client.print(pm25val);
      client.println(" HTTP/1.1");
      client.println("Host: wxxcc.applinzi.com");
      client.println("User-Agent: arduino-ethernet");
      client.println("Connection: close");
      client.println();
      lastConnectionTime = millis();
    }else {
      //Serial.println("connection failed");
      //Serial.println("disconnecting.");
      client.stop();
    }
  }
  lastConnected = client.connected();
 }
