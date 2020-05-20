#include <esp32-pwm.h>

#define LED 17 //On Board LED
ESP32_PWM * brightness;

//=======================================================================
//                    Power on setup
//=======================================================================
void setup() {
  brightness = new ESP32_PWM(LED);
  Serial.begin(115200);
}
 
//=======================================================================
//                    Main Program Loop
//=======================================================================
void loop() {
  
 for (int x = 0;x<=100;x++){
  
  Serial.println(brightness->setBrightness(x));
  
  }
  delay(50);
 
}
