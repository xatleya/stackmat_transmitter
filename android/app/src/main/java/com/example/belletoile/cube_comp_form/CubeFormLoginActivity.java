package com.example.belletoile.cube_comp_form;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.util.Log;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.EditText;
import android.widget.Toast;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

import utils.Constants;
import utils.PreferenceUtils;

public class CubeFormLoginActivity extends Activity implements OnClickListener {

    // The EditText in which the user login
    private EditText login;

    // The EditText in which the user type password
    private EditText password;

    // DATABASE
    private RequestQueue requestQueue;
    private static final String URL = Constants.General.LINK + "user_control.php";
    private StringRequest request;

    protected void onResume(){
        super.onResume();
        login = (EditText) findViewById(R.id.loginEditText);
        login.setText("");
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);


        // Load our XML layout to display GUI
        setContentView(R.layout.activity_login);

        // Keep a reference to the EditText
        login = (EditText) findViewById(R.id.loginEditText);
        password = (EditText) findViewById(R.id.passwordEditText);

        requestQueue = Volley.newRequestQueue(this);

        // Now add a listener when we click on the Login button
        findViewById(R.id.loginButton).setOnClickListener(this);

        final String storedLogin = PreferenceUtils.getLogin();
        final String storedPassword = PreferenceUtils.getPassword();
        if ((!TextUtils.isEmpty(storedLogin)) && (!TextUtils.isEmpty(storedPassword))) {
            final Intent homeIntent = getHomeActivityIntent(storedLogin, storedPassword);
            startActivity(homeIntent);
        }
    }

    @Override
    public void onClick(View view) {
        // Check if a login is set
        if (TextUtils.isEmpty(login.getText())) {
            // Display a Toast to the user
            Toast.makeText(this, R.string.error_no_login, Toast.LENGTH_LONG).show();
            return;
        }

        // Check if a password is set
        if (TextUtils.isEmpty(password.getText())) {
            // Display a Toast to the user
            Toast.makeText(this, R.string.error_no_password, Toast.LENGTH_LONG).show();
            return;
        }

        request = new StringRequest(Request.Method.POST, URL, new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject jsonObject = new JSONObject(response);
                    if(jsonObject.names().get(0).equals("success")){
                        Toast.makeText(getApplicationContext(), "SUCCESS"+jsonObject.getString("success"), Toast.LENGTH_SHORT).show();
                        // Before launching the second Activity, just save the values in SharedPreferences
                        PreferenceUtils.setLogin(login.getText().toString());
                        PreferenceUtils.setPassword(password.getText().toString());

                        // Here we are, a login and password are set, try to login
                        // For now just launch the second activity, to do that create an Intent
                        final Intent homeIntent = getHomeActivityIntent(login.getText().toString(), password.getText().toString());
                        startActivity(homeIntent);
                    }
                    else{
                        Toast.makeText(getApplicationContext(), "Error"+jsonObject.getString("error"), Toast.LENGTH_SHORT).show();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }, new Response.ErrorListener(){
            @Override
            public void onErrorResponse(VolleyError error){
                error.printStackTrace();
            }
        }){
            @Override
            protected Map<String, String> getParams() throws AuthFailureError{
                HashMap<String, String> hashMap = new HashMap<String, String>();
                hashMap.put("login", login.getText().toString());
                hashMap.put("password", password.getText().toString());

                return hashMap;
            }
        };

        requestQueue.add(request);
    }

    private Intent getHomeActivityIntent(String userName, String pass) {
        final Intent intent = new Intent(this, CubeFormActivity.class);
        final Bundle extras = new Bundle();
        extras.putString(Constants.Login.EXTRA_LOGIN, userName);
        extras.putString(Constants.Password.EXTRA_PASSWORD, pass);
        intent.putExtras(extras);
        return intent;
    }
}
