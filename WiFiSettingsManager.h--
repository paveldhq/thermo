#ifndef THERMO_WIFI_SETTINGS_MANAGER
#define THERMO_WIFI_SETTINGS_MANAGER

#include "Arduino.h"
#include "String.h"

#ifndef WIFIFILE
#define WIFIFILE "/wifi.json"
#endif

enum WIFI_MODE {
  client,
  ap
};

enum WIFI_PROTECTION {
  none,
  wpa
};

struct WIFI_SETTINGS
{
  String *        ssid;
  WIFI_MODE       mode;
  WIFI_PROTECTION protection;
  String *        password;
};


class WifiSettingsManager
{
  public:
    WifiSettingsManager(WIFI_SETTINGS settings);
    bool start();
    bool stop();
    void setSettings(WIFI_SETTINGS settings);
    WIFI_SETTINGS getSettings(void);
    String getDefaultWifiSettingsJson(WIFI_SETTINGS defaultSettings);
  private:
    WIFI_SETTINGS _settins;
};

#endif
