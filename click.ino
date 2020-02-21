
#define BUTTON_PIN 0

#define SHRT_UP_MS 50
#define LONG_UP_MS 800

static uint32_t tStart = 0;
static uint32_t tFinish = 0;


void IRAM_ATTR _click();

bool checkClick = false;


void setup() {
  Serial.begin(115200);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  attachInterrupt(BUTTON_PIN, _click, CHANGE);
}

void IRAM_ATTR _click() {
  if ( 0 == digitalRead(BUTTON_PIN)) {
    // нажали
    tStart = millis();
    tFinish = 0;
  } else {
    // отпустили
    tFinish = millis();
    checkClick = !checkClick;
  }
}

void clickShort() {
  Serial.println("Short");
}
void clickLong() {
  Serial.println("Long");
}

void clickHandler() {
  if ( tFinish != 0 ) {
    uint32_t _delay = tFinish - tStart;
    tFinish = tStart = 0;

    if (_delay < SHRT_UP_MS) {
      return;
    }

    if (_delay >= LONG_UP_MS) {
      clickLong();
    } else {
      clickShort();
    }
    return;

  }
}

void loop() {
  delay(10);
  if (checkClick) {
    checkClick = !checkClick;
    clickHandler();
  }
}
