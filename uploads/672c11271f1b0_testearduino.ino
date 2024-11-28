#include <WiFi.h>
#include <FirebaseESP32.h>
#include "addons/TokenHelper.h"


const char* ssid = "CLARA";
const char* password = "92735474";

FirebaseData firebaseData;
FirebaseConfig firebaseConfig; 
FirebaseAuth firebaseAuth;

const int p34 = 34;
bool signupOK = false;

void setup() {
  Serial.begin(115200);
  pinMode(p34, INPUT); 

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Conectando ao WiFi...");
  }
  
  Serial.println("Conectado ao WiFi");
  
  firebaseConfig.api_key = "AIzaSyCCnJeVGGuKL68HT_vUvjg4M3jdA0Si0Es";
  firebaseConfig.database_url ="https://fir-demo-c941d-default-rtdb.firebaseio.com/";
  
 if (Firebase.signUp(&firebaseConfig, &firebaseAuth, "", "")) {
    Serial.println("ok");
    signupOK = true;
  }
  else {
    Serial.printf("%s\n", firebaseConfig.signer.signupError.message.c_str());
  }

  Firebase.begin(&firebaseConfig, &firebaseAuth);
  Firebase.reconnectWiFi(true);
}


void loop() {
  int leitura = analogRead(p34);
  float umidade = map(leitura, 0, 4095, 100, 0);

  Serial.print("Umidade do solo: ");
  Serial.print(umidade);
  Serial.println("%");

 if (Firebase.setInt(firebaseData, "/sensor/umidade", umidade)) {
    Serial.println("Dados enviados para o Firebase");
  } else {
    Serial.print("Falha ao enviar para o Firebase: ");
    Serial.println(firebaseData.errorReason());
  }
  delay(1000);
}
