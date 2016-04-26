void setup() {
  // put your setup code here, to run once:
  for(int i = 0; i <= 13; i++) {
    pinMode(i,OUTPUT); //set all the pins to input mode
  }
}

void loop() {
  // put your main code here, to run repeatedly:
  for(int i = 0; i <= 13; i++) {
    digitalWrite(i,LOW); //set all the pins to input mode
  }
}
