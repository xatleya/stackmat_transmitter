package com.example.belletoile.cube_comp_form;

import android.app.Application;
import android.content.Context;

public class CubeFormApplication extends Application{

    private static Context context;

    public void onCreate(){
        super.onCreate();

        //Keep a reference to the application context
        context = getApplicationContext();
    }

    //Used to access the context anywhere within the app
    public static Context getContext(){
        return context;
    }
}
