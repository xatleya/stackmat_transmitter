#include <SoftwareSerial.h>
int t =1;
int count =0;
int isTheSame = 0;
int sendNumber =0;
int canSend = 1;
SoftwareSerial ESP8266(10, 11);
String message = "";
char buf[10];
        char num[6];
        char checkSum;

//String NomduReseauWifi = "Livebox-FB2A"; // Garder les guillements
//String MotDePasse      = "17E3AAF1D19EEA99727C9194A9"; // Garder les guillements
String NomduReseauWifi = "WIFINEWEPS"; // Garder les guillements
String MotDePasse      = "";

  /****************************************************************/
    /*                Fonction qui initialise l'ESP8266             */
    /****************************************************************/
    void initESP8266()
    {  

      
      Serial.println("**********************************************************");  
      Serial.println("**************** DEBUT DE L'INITIALISATION ***************");
      Serial.println("**********************************************************");  
      envoieAuESP8266("AT");
      recoitDuESP8266(1000);
      Serial.println("**********************************************************");
      envoieAuESP8266("AT+CWMODE=1");
      recoitDuESP8266(1000);
     
      Serial.println("**********************************************************");
      envoieAuESP8266("AT+CWJAP=\""+ NomduReseauWifi + "\",\"" + MotDePasse +"\"");
     //  envoieAuESP8266("AT+CWJAP=\""+ NomduReseauWifi + "\"");
      recoitDuESP8266(7000);
     
      Serial.println("**********************************************************");
      envoieAuESP8266("AT+CIFSR");
      recoitDuESP8266(1000);
      Serial.println("**********************************************************");
      envoieAuESP8266("AT+CIPMUX=1");   
      recoitDuESP8266(1000);
      Serial.println("**********************************************************");
      Serial.println("**********************************************************");
      Serial.println("***************** INITIALISATION TERMINEE ****************");
      Serial.println("**********************************************************");
      Serial.println("");  
   /*   envoieAuESP8266("AT+CIPSTART=1,\"TCP\",\"192.168.1.12\",2300");
      recoitDuESP8266(3000);
      envoieAuESP8266("AT+CIPSEND=1,4");
      recoitDuESP8266(3000);
         envoieAuESP8266("AT+CIPSEND");
      recoitDuESP8266(3000);
      envoieAuESP8266("AT+CIPSTART=1,\"TCP\",\"192.168.1.12\",2300");
      recoitDuESP8266(3000);*/
    }


/****************************************************************/
    /*        Fonction qui envoie une commande à l'ESP8266          */
    /****************************************************************/
    void envoieAuESP8266(String commande)
    {  
      ESP8266.println(commande);
    }
    /****************************************************************/
    /*Fonction qui lit et affiche les messages envoyés par l'ESP8266*/
    /****************************************************************/
    void recoitDuESP8266(const int timeout)
    {
      String reponse = "";
      long int time = millis();
      while( (time+timeout) > millis())
      {
        while(ESP8266.available())
        {
          char c = ESP8266.read();
          reponse+=c;
        }
      }
      Serial.print(reponse);   
    }

  /****************************************************************/
    /*                             INIT                             */
    /****************************************************************/
    void setup()
    {
      Serial.begin(1200);
      //Serial.begin(9600);
       ESP8266.begin(115200);
      envoieAuESP8266("AT+CIOBAUD=9600");
      recoitDuESP8266(4000);
      
      ESP8266.begin(9600);  
      initESP8266();
      //Serial.begin(1200); 
    
    }
    /****************************************************************/
    /*                        BOUCLE INFINIE                        */
    /****************************************************************/
    void loop()
    {
          

        if (t==1){
                
              
                 if (Serial.available()){
                    Serial.readBytesUntil(0x0D, buf, 10);

                    if(num[0]==buf[1] && num[1]==buf[2] && num[2]== buf[3] && num[3]==buf[4] && num[4]==buf[5] && num[5]==buf[6] && (num[1]!='0' || num[2]!='0')){
                        isTheSame = 1;
                        
                      }
                   
                    num[0]=  buf[1];
                    num[1] =  buf[2];
                    num[2]=  buf[3];
                    num[3]=  buf[4];
                    num[4] = buf[5];
                    num[5] =  buf[6];
                    checkSum =  buf[7];
                    char sum = num[0]+num[1]+num[2]+num[3]+num[4]+num[5]+32;
                //    Serial.println(checkSum);
                  //  Serial.println(sum);
                  //  Serial.println("ok");
                    if (sum==checkSum){
                    Serial.println(buf[1]);
                 Serial.println(buf[2]);
               
                Serial.println(buf[3]);
                 Serial.println(buf[4]);
                 Serial.println(buf[5]);
                 Serial.println(buf[6]);
                 Serial.println(buf[7]);
               //  Serial.println(buf[8]);
                 Serial.println(" ");
                    if (isTheSame==1){
                          count++;
                          isTheSame =0;
                          
                      }
                      else{
                          count = 0;
                          canSend = 1;
                        }
                       
                    }
                    
                 
                 }

                 if (count==3 && canSend == 1){t=3;}
        }

      if (t==2 && canSend == 1){
          count = 0;
          
          envoieAuESP8266("AT+CIPSTART=1,\"TCP\",\"192.167.7.68\",2300");
      recoitDuESP8266(1000);
      envoieAuESP8266("AT+CIPSEND=1,8");
      recoitDuESP8266(1000);c
     
      envoieAuESP8266("\""+message+"\"");
      recoitDuESP8266(1000);
      sendNumber++;
      //   envoieAuESP8266("AT+CIPSEND");
    //  recoitDuESP8266(3000);
     // envoieAuESP8266("AT");
     
      //recoitDuESP8266(3000);
       while(ESP8266.available())
       {    
         Serial.println(ESP8266.readString());
       }   
       if (sendNumber==1){
          sendNumber=0;
          canSend = 0;
          t=1;
          
          //envoieAuESP8266("AT+SLEEP=2");
          //recoitDuESP8266(3000);
        
         // Serial.begin(1200);
        }
      }


      if (t==3){

        
        message="";
        message += 'O';
        message += String(num[0]);
        message += String(num[1]);
        message+= String (num[2]);
        message+= String (num[3]);
        message += String(num[4]);
        message += String(num[5]);

       /* if (isInit!=1){
       // Serial.begin(9600);
           ESP8266.begin(115200);
      envoieAuESP8266("AT+CIOBAUD=9600");
      recoitDuESP8266(2000);
      
      ESP8266.begin(9600);  
      initESP8266();
        }
       isInit = 1;*/
      t=2;
      }
      //Serial.begin(1200); 
        
        
    }
  
    
