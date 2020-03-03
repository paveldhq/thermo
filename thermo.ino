#include <String.h>
#include <WiFiClient.h>
#include <ESPmDNS.h>
#include <Update.h>
#include <ArduinoOTA.h>
#include <FS.h>
#include <SPIFFS.h>

#ifdef ESP32
  #include <ESPmDNS.h>
  #include <WiFi.h>
  #include "AsyncTCP.h"
#elif defined(ESP8266)
  #include <ESP8266WiFi.h>
  #include <ESPAsyncTCP.h>
  #include <ESP8266mDNS.h>
#endif

#include <ESPAsyncWebServer.h>
//#include <SPIFFSEditor.h>

#ifndef THERMO_VERSION
  #define THERMO_VERSION "0.0.1"
#endif

#ifndef SERVER_PORT
  #define SERVER_PORT 80
#endif

#ifndef VERSIONFILE
  #define VERSIONFILE "/version.json"
#endif


void debug(String * message);

#include "WifiSettingsManager.h"


WIFI_SETTINGS defaultSettings = { new String("thermo"), WIFI_MODE::ap, WIFI_PROTECTION::none, new String("") };

WIFI_SETTINGS wifiSettings;

AsyncWebServer server(SERVER_PORT);

#ifdef DEBUG
void debug(String * message) {
  Serial.println(message);
}

#else
  void debug(String * message) {
}

#endif

const char* host = "esp32";

WIFI_SETTINGS getSettings()
{
}



String * readFile(String * file, String * defaultValue) {
  File handler = SPIFFS.open(file->c_str(), "r");
  if (!handler) {
  }
  
}



/*
   setup function
*/
void setup(void) {
  Serial.begin(115200);

  // Connect to WiFi network
 // WiFi.begin(wifiSettings.ssid, wifiSettings.password);
  Serial.println("");

  // Wait for connection
  //while (WiFi.status() != WL_CONNECTED) {
  //  delay(500);
  //  Serial.print(".");
  //}
  //Serial.println("");
  //Serial.print("Connected to ");
  //Serial.println(ssid);
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  /*use mdns for host name resolution*/
  //if (!MDNS.begin(host)) { //http://esp32.local
  //  Serial.println("Error setting up MDNS responder!");
  //  while (1) {
  //    delay(1000);
  //  }
  ////}
 // Serial.println("mDNS responder started");
  /*return index page which is stored in serverIndex */

  server.begin();
}

void loop(void) {
  //server.handle();
  delay(1);
  wifiSettings.
}
