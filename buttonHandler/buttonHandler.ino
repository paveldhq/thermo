
#define BUTTON_PIN 0

#define SHRT_UP_MS    50
#define LONG_UP_MS    800
#define LONG_UP_AUTO  3000


#define BUTTON_AUTO_UP_TIMER 0
#include "Button.h"
#include "init.h"

void setup() {
  Serial.begin(115200);

 button = new Button(
  new ButtonDefinition(
{
  BUTTON_PIN,
  BUTTON_AUTO_UP_TIMER,
  SHRT_UP_MS,
  LONG_UP_MS,
  LONG_UP_AUTO,
  &clickShort,
  &clickLong
}
  )
);

}

void clickShort() {
  Serial.println("Short");
}

void clickLong() {
  Serial.println("Long");
}

void loop() {
  delay(10);
}
