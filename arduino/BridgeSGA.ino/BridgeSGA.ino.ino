#include <Bridge.h>
#include <BridgeServer.h>
#include <BridgeClient.h>

BridgeServer server;

char* positions[] = {"President","VP","Webmaster","Exec Assistant","CFO","MCD","SAC","PAC","ADC","MEC","SOC","DCC","VN","BORSC"};

void setup() {
  // put your setup code here, to run once:
  Bridge.begin();
  for(int i = 0; i <= 13; i++) {
    pinMode(i,INPUT); //set all the pins to input mode
  }
}

void loop() {
  // put your main code here, to run repeatedly:
  int values[14];
  for(int i = 0; i <= 13; i++) {
    values[i] = digitalRead(i); //get all the values from the arduino
  }
  for (int i = 0; i <= 13; i++) {
    String key = String(positions[i]);
    String value = String(values[i]);
    Bridge.put(key, value);
  }
  delay(50); //wait a bit before doing anything, just so we don't overload it
}
