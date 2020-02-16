#include "WifiSettingsManager.h"
#include <ArduinoJson.h>


WifiSettingsManager::WifiSettingsManager(WIFI_SETTINGS settings) {};
bool WifiSettingsManager::start() {};
bool WifiSettingsManager::stop() {};
void WifiSettingsManager::setSettings(WIFI_SETTINGS settings) {};
WIFI_SETTINGS WifiSettingsManager::getSettings(void) {};


String WifiSettingsManager::getDefaultWifiSettingsJson(WIFI_SETTINGS defaultSettings) {
  StaticJsonDocument<1024> doc;
  doc["ssid"]       = defaultSettings.ssid;
  doc["mode"]       = defaultSettings.mode == WIFI_MODE::ap ? "ap" : "client";
  doc["protection"] = defaultSettings.protection == WIFI_PROTECTION::none ? "none" : "wpa";
  doc["password"]   = defaultSettings.password;
  
  String  out;
  serializeJson(doc, out);
  return out;
};
