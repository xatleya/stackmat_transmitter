package com.example.belletoile.cube_comp_form;

import android.os.AsyncTask;
import android.support.annotation.NonNull;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import com.example.belletoile.cube_comp_form.CubeFormActivity;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintStream;
import java.net.ServerSocket;
import java.net.Socket;
import java.nio.ByteOrder;
import java.nio.CharBuffer;

/**
 * Created by ISEN on 02/04/2017.
 */

public class SocketAsyncTask extends AsyncTask<ServerSocket,Void,Void> {
    protected String time, timeCubeComps ;
    protected CubeFormActivity acti;
    protected Socket socketClient = null;

    public SocketAsyncTask(CubeFormActivity activity) {
        acti=activity;
    }

    @Override
    protected Void doInBackground(ServerSocket... sockets) {
        try {
            while (true) {
                socketClient = sockets[0].accept();
                char[] message = new char[10];

                //Toast toast = Toast.makeText(getApplicationContext(),"Connexion avec : "+socketClient.getInetAddress() , Toast.LENGTH_LONG);
                // toast.show();
                System.out.println("Connexion avec : " + socketClient.getInetAddress());
                BufferedReader in = new BufferedReader(
                        new InputStreamReader(socketClient.getInputStream()));
                PrintStream out = new PrintStream(socketClient.getOutputStream());

                in.read(message) ;
               // out.println(message);
               System.out.println(message);
                //System.out.println(message[0]);
                time = "";
                timeCubeComps = "";
                for (int i =2;i<8;i++) {

                    time += Character.toString(message[i]);
                    switch (i){
                        case 2 :
                            timeCubeComps += Character.toString(message[i]);
                            timeCubeComps += ":";
                            break;
                        case 4 :
                            timeCubeComps += Character.toString(message[i]);
                            timeCubeComps += ":";
                            break;
                        case 7 :
                            break;
                        default :
                            timeCubeComps += Character.toString(message[i]);
                            break;

                    }
                }
                if (message[1]=='O'){
                    socketClient.close();
                    return null;
                }
                //socketClient.close();
            }
        }
     catch (Exception e) {

        e.printStackTrace();

    }
        return null;
    }

    @Override
    protected void onPostExecute(Void aVoid) {
        super.onPostExecute(aVoid);
        timeCubeComps = timeCubeComps.substring(0,4) + "." + timeCubeComps.substring(5);
        EditText current_editText = this.acti.getCurrentTextBox();
        if(current_editText != null){
            if(CubeFormActivity.checkTimeFormat(timeCubeComps)){
                this.acti.getCurrentTextBox().setText(timeCubeComps);
                Toast.makeText(CubeFormApplication.getContext(), "ok", Toast.LENGTH_SHORT).show();
            }
            else{
                Toast.makeText(CubeFormApplication.getContext(), "non ok", Toast.LENGTH_SHORT).show();
            }
            try {
                if(socketClient != null){
                    socketClient.close();
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
        else{
            Toast.makeText(CubeFormApplication.getContext(), "error in send", Toast.LENGTH_SHORT).show();
        }
        try{
            new SocketAsyncTask(acti).execute(acti.getSocketServeur());}
        catch (Exception e){

        }
    }
}
