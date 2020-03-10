#include "Button.h"

Button::Button(ButtonDefinition * definition) {
  _definition = definition;
  this->init();
}

void Button::init() {
  pinMode(this->_definition->pin, INPUT_PULLUP);
  attachInterrupt(this->_definition->pin, this->&_click, CHANGE);
}

void IRAM_ATTR Button::_click() {
  if ( 0 == digitalRead(this->_definition->pin)) {
    // нажали
    this->tStart = millis();
    this->timerActivate();
    this->tFinish = 0;
  } else {
    // отпустили
    this->tFinish = millis();
    this->timerDeactivate();
    this->clickHandler();
  }
}

void Button::timerActivate() {
  if (!this->timerActive) {
    this->timer = timerBegin(this->_definition->timerId, AUTO_PRESCALER, true);
    timerAttachInterrupt(this->timer, this->&timerHandler, true);
    timerAlarmWrite(this->timer, this->_definition->autoRelease, true);
    timerAlarmEnable(this->timer);
    this->invertTimerState();
  }
}

void IRAM_ATTR Button::timerHandler() {
  this->timerDeactivate();
  this->tFinish = millis();
  this->clickHandler();
}

void Button::clickHandler() {
  if ( this->tFinish != 0 && this->tStart > 0 ) {
    uint32_t _delay = this->tFinish - this-> tStart;
    this->tFinish = this->tStart = 0;

    if (_delay < this->_definition->shortIgnore) {
      return;
    }

    if (_delay >= this->_definition->longClick) {
      this->_definition->longClickHandler();
    } else {
      this->_definition->shortClickHandler();
    }
    return;

  }
}



void Button::invertTimerState()
{
  this->timerActive = !this->timerActive;
}



void Button::timerDeactivate() {
  if (this->timerActive) {
    timerAlarmDisable(this->timer);
    timerDetachInterrupt(this->timer);
    timerEnd(this->timer);
    this->invertTimerState();
  }
}
