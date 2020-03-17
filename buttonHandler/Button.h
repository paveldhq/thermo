
#ifndef __BUTTON_LIB_
#define __BUTTON_LIB_

#include "Arduino.h";

#define AUTO_PRESCALER F_CPU / 1000

struct ButtonDefinition {
  uint8_t pin;
  uint8_t timerId;
  uint32_t shortIgnore;
  uint32_t longClick;
  uint32_t autoRelease;
  void  (* shortClickHandler)();
  void  (* longClickHandler)();
};

class Button {
  private:
    uint32_t tStart = 0;
    uint32_t tFinish = 0;
    ButtonDefinition * _definition;
    hw_timer_t * timer;
    void init();
    void IRAM_ATTR _click();
    void IRAM_ATTR timerHandler();
    bool timerActive = false;
    void invertTimerState();
    void clickHandler();
    void timerActivate();
    void timerDeactivate();
  public:
    Button(ButtonDefinition * definition);
};

#endif /* __BUTTON_LIB_ */
