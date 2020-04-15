#include <SPI.h>
#include <TFT_eSPI.h>
TFT_eSPI tft = TFT_eSPI();
struct Button {
  const uint8_t PIN;
  uint32_t numberKeyPresses;
  bool pressed;
};

Button button1 = {39, 0, false};

void IRAM_ATTR isr() {
  button1.numberKeyPresses += 1;
  button1.pressed = true;
}

void setup() {
  Serial.begin(115200);
  pinMode(button1.PIN, INPUT_PULLUP);
  attachInterrupt(button1.PIN, isr, FALLING);
  Serial.println("\n\nStarting...");

  tft.init();
}

void loop() {
  if (button1.pressed) {
     uint16_t x, y;

  tft.getTouchRaw(&x, &y);
  
  Serial.printf("x: %i     ", x);

  Serial.printf("y: %i     ", y);

  Serial.printf("z: %i \n", tft.getTouchRawZ());

  delay(250);
      button1.pressed = false;
  }

  //Detach Interrupt after 1 Minute
  static uint32_t lastMillis = 0;
  if (millis() - lastMillis > 60000) {
    lastMillis = millis();
    detachInterrupt(button1.PIN);
  Serial.println("Interrupt Detached!");
  }
}
