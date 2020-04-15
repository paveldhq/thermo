
#include "FS.h"
#include "SD.h"
#include "SPI.h"

SPIClass spiSD(HSPI);

#define SD_CS 15

void setup() {
Serial.begin(115200);
spiSD.begin(14, 26, 13, 15); //SCK,MISO,MOSI,SS //HSPI1
if (!SD.begin( SD_CS, spiSD )) {
// if(!SD.begin()){
Serial.println("Card Mount Failed");
return;
} else{Serial.println("      OK   :)");}
}

void loop() {
}
