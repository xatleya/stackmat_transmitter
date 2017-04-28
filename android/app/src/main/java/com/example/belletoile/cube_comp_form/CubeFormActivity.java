package com.example.belletoile.cube_comp_form;

import android.app.Activity;
import android.content.Intent;
import android.net.wifi.WifiInfo;
import android.net.wifi.WifiManager;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.util.Log;
import android.view.KeyEvent;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewManager;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.EditText;
import android.widget.RelativeLayout;
import android.widget.Spinner;
import android.widget.TextView;
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
import org.w3c.dom.Text;

import java.io.IOException;
import java.net.InetSocketAddress;
import java.net.ServerSocket;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import utils.Constants;
import utils.PreferenceUtils;

public class CubeFormActivity extends Activity implements View.OnClickListener{

    private String login;
    private String password;

    private Spinner spinner_event;
    private Spinner spinner_name;

    private TextView round_format_text;
    private TextView time_limit_text;

    private int times;
    private String timelimit;
    private boolean limitTimeReach = false;

    // data to send
    private String event_selected;
    private String competitor_selected;
    private String round;

    //data of the spinner_name
    private ArrayAdapter<String> dataAdapter;

    // DATABASE
    private RequestQueue requestQueue;
    private StringRequest request;
    private static final String URL_events = Constants.General.LINK + "get_json_events.php";
    private static final String URL_competitors = Constants.General.LINK + "get_json_competitors.php";
    private static final String URL_times = Constants.General.LINK + "send_time.php";

    //items for the time
    private RelativeLayout relativeLayouts[];
    private EditText editTexts[];
    private CheckBox checkBoxes[];

    private int currentTime = 1;

    // STACKMAT
    public ServerSocket socketServeur;
    private String ipAdress;
    private SocketAsyncTask sat;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        // Load our XML layout to display GUI
        setContentView(R.layout.activity_second);

        findViewById(R.id.submit_time).setOnClickListener(this);

        spinner_event = (Spinner)findViewById(R.id.spinner_categories);
        spinner_name = (Spinner)findViewById(R.id.spinner_name);

        round_format_text = (TextView) findViewById(R.id.textRoundFormat);
        time_limit_text = (TextView) findViewById(R.id.textCutoff);

        relativeLayouts = new RelativeLayout[5];
        editTexts = new EditText[5];
        checkBoxes = new CheckBox[5];

        int[] relativesId = new int[] {R.id.setTime1, R.id.setTime2, R.id.setTime3, R.id.setTime4, R.id.setTime5 };
        final int[] editTextId = new int[] {R.id.time1, R.id.time2, R.id.time3, R.id.time4, R.id.time5 };
        final int [] checkBoxId = new int[] {R.id.check1, R.id.check2, R.id.check3, R.id.check4, R.id.check5 };

        for(int i=0;i<5;i++){
            relativeLayouts[i] = (RelativeLayout) findViewById(relativesId[i]);
            editTexts[i] = (EditText) findViewById(editTextId[i]);

            checkBoxes[i] = (CheckBox) findViewById(checkBoxId[i]);
            //CHECKBOX LISTENER
            checkBoxes[i].setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
                @Override
                public void onCheckedChanged(CompoundButton buttonView,boolean isChecked) {
                    if(isChecked){
                        if(checkTimeFormat(editTexts[currentTime-1].getText().toString())){
                            if(currentTime == 1){
                                spinner_event.setEnabled(false);
                            }
                            else{
                                checkBoxes[currentTime-2].setEnabled(false);
                            }
                            editTexts[currentTime-1].setEnabled(false);
                            if(currentTime != times){
                                editTexts[currentTime].setEnabled(true);
                                checkBoxes[currentTime].setEnabled(true);
                            }
                            if(currentTime==2 && !timelimit.equals("-1") && times==5){
                                String et0 = editTexts[0].getText().toString();
                                String et1 = editTexts[1].getText().toString();
                                if((et0.equals("DNF") || et0.equals("dnf") || et0.equals("DNS") || et0.equals("dns") || isLimitReach(timelimit, editTexts[0].getText().toString())) && (et1.equals("DNF") || et1.equals("dnf") || et1.equals("DNS") || et1.equals("dns") || isLimitReach(timelimit, editTexts[1].getText().toString()))){
                                    for(int j=2;j<5;j++){
                                        relativeLayouts[j].setVisibility(View.GONE);
                                    }
                                    limitTimeReach = true;
                                }

                            }
                            if(currentTime==1 && !timelimit.equals("-1") && times==3){
                                String et0 = editTexts[0].getText().toString();
                                if(et0.equals("DNF") || et0.equals("dnf") || et0.equals("DNS") || et0.equals("dns") || isLimitReach(timelimit, editTexts[0].getText().toString())){
                                    for(int j=1;j<3;j++){
                                        relativeLayouts[j].setVisibility(View.GONE);
                                    }
                                    limitTimeReach = true;
                                }
                            }
                            currentTime++;
                        }
                        else{
                            Toast.makeText(getApplicationContext(), "Bad format : 0:00.00 needed", Toast.LENGTH_SHORT).show();
                            checkBoxes[currentTime-1].setChecked(false);
                        }
                    }
                    else {
                        currentTime--;
                        if(limitTimeReach){
                            limitTimeReach = false;
                            for(int j=currentTime;j<times;j++){
                                relativeLayouts[j].setVisibility(View.VISIBLE);
                            }
                        }
                        if(currentTime==1){
                            spinner_event.setEnabled(true);
                        }
                        else{
                            checkBoxes[currentTime-2].setEnabled(true);
                        }
                        if(currentTime != times){
                            editTexts[currentTime].setText("");
                            editTexts[currentTime].setEnabled(false);
                            checkBoxes[currentTime].setEnabled(false);
                        }
                        editTexts[currentTime-1].setEnabled(true);
                    }
                 }
            }
            );


        }


        requestQueue = Volley.newRequestQueue(this);

        // Retrieve the login passed as parameter
        final Intent intent = getIntent();
        if (null != intent){
            final Bundle extras = intent.getExtras();
            if ((null != extras) && (extras.containsKey(Constants.Login.EXTRA_LOGIN))){
                // Retrieve the login
                this.login = extras.getString(Constants.Login.EXTRA_LOGIN);
                this.password = extras.getString(Constants.Password.EXTRA_PASSWORD);


                // Set as ActionBar subtitle
                getActionBar().setSubtitle(this.login);
            }
        }

        request = new StringRequest(Request.Method.POST, URL_events, new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject jsonObject = new JSONObject(response);
                    //Toast.makeText(getApplicationContext(), "SUCCESS"+jsonObject.getString("count"), Toast.LENGTH_SHORT).show();
                    int events_count = Integer.parseInt(jsonObject.getString("count"));
                    String[] events = new String[events_count];
                    String e_key;
                    for(int i=0;i<events_count;i++){
                        e_key = "event" + i;
                        events[i] = jsonObject.getString(e_key);
                    }

                    addItemsOnSpinner(events, events_count);
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
            protected Map<String, String> getParams() throws AuthFailureError {
                HashMap<String, String> hashMap = new HashMap<String, String>();
                hashMap.put("login", login);

                return hashMap;
            }
        };

        requestQueue.add(request);

        // STACKMAT
        WifiManager wifiManager = (WifiManager) getSystemService(WIFI_SERVICE);
        WifiInfo wifiInfo = wifiManager.getConnectionInfo();
        String ipAddress = wifiInfo.getMacAddress();


        //Toast toast = Toast.makeText(getApplicationContext(),Integer.toString(wifiInfo.getIpAddress()) , Toast.LENGTH_LONG);
        // toast.show();
        int ip = wifiInfo.getIpAddress();

        ipAddress = String.format("%d.%d.%d.%d", (ip & 0xff), (ip >> 8 & 0xff), (ip >> 16 & 0xff),
                (ip >> 24 & 0xff));
        Toast.makeText(getApplicationContext(), "IP : " + ipAddress, Toast.LENGTH_SHORT).show();

        try {

            socketServeur = new ServerSocket();
            socketServeur.setReuseAddress(true);
            sat = new SocketAsyncTask(this);
            sat.execute(socketServeur);
            socketServeur.bind(new InetSocketAddress(2300));
        }

        catch (Exception e) {
            e.printStackTrace();
        }
    }

    @Override
    public void onResume() {
        super.onResume();  // Always call the superclass method first
        try {

            /*socketServeur = new ServerSocket();
            socketServeur.setReuseAddress(true);
            sat = new SocketAsyncTask(this);
            sat.execute(socketServeur);
            socketServeur.bind(new InetSocketAddress(2300));*/

            socketServeur.setReuseAddress(true);
            sat.execute(socketServeur);
            socketServeur.bind(new InetSocketAddress(2300));
        }

        catch (Exception e) {
            e.printStackTrace();
        }

    }

    public ServerSocket getSocketServeur() {
        return socketServeur;
    }



    public void addItemsOnSpinner(String[] items, int size){
        // Spinner Drop down elements
        List<String> list = new ArrayList<String>();
        for(int i=0;i<size;i++){
            list.add(items[i]);
        }
        // Creating adapter for spinner_event
        ArrayAdapter<String> dataAdapter = new ArrayAdapter<String>(this,android.R.layout.simple_spinner_item, list);
        // Drop down layout style - list view with radio button
        dataAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        // attaching data adapter to spinner_event
        spinner_event.setAdapter(dataAdapter);

        spinner_event.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                final String item_selected = parent.getItemAtPosition(position).toString();
                /*Toast.makeText(parent.getContext(), "OnItemSelectedListener : " + item_selected,
                        Toast.LENGTH_SHORT).show();*/
                event_selected = item_selected;

                request = new StringRequest(Request.Method.POST, URL_competitors, new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        try {
                            JSONObject jsonObject = new JSONObject(response);
                            //Toast.makeText(getApplicationContext(), "SUCCESS"+jsonObject.getString("count"), Toast.LENGTH_SHORT).show();
                            int comps_count = Integer.parseInt(jsonObject.getString("count"));
                            String[] competitors = new String[comps_count];
                            String c_key;
                            for(int i=0;i<comps_count;i++){
                                c_key = "comp" + i;
                                competitors[i] = jsonObject.getString(c_key);
                            }

                            Arrays.sort(competitors);

                            addItemsOnSpinner2(competitors, comps_count);

                            round = jsonObject.getString("round");
                            String format = jsonObject.getString("format");
                            times = Integer.parseInt(jsonObject.getString("times"));
                            round_format_text.setText("Round " + round + " - " + format);
                            for(int i=0;i<times;i++){
                                relativeLayouts[i].setVisibility(View.VISIBLE);
                            }
                            for(int i=times;i<5;i++){
                                relativeLayouts[i].setVisibility(View.GONE);
                            }
                            currentTime = 1;
                            timelimit = jsonObject.getString("timelimit");
                            if(timelimit.equals("-1")){
                                time_limit_text.setText("no cutoff");
                            }
                            else{
                                timelimit = timelimit.substring(1);
                                timelimit = timelimit.substring(0,4) + "." + timelimit.substring(5);
                                time_limit_text.setText(timelimit);
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
                    protected Map<String, String> getParams() throws AuthFailureError {
                        HashMap<String, String> hashMap = new HashMap<String, String>();
                        hashMap.put("login", login);
                        hashMap.put("event", item_selected);

                        return hashMap;
                    }
                };

                requestQueue.add(request);
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {
                //nothing for now
            }
        });
    }

    public void addItemsOnSpinner2(String[] items, int size){
        if(this.dataAdapter != null){
            this.dataAdapter.clear();
            this.dataAdapter.notifyDataSetChanged();
        }
        // Spinner Drop down elements
        List<String> list = new ArrayList<String>();
        for(int i=0;i<size;i++){
            list.add(items[i]);
        }
        // Creating adapter for spinner_event
        this.dataAdapter = new ArrayAdapter<String>(this,android.R.layout.simple_spinner_item, list);
        // Drop down layout style - list view with radio button
        dataAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);

        // attaching data adapter to spinner_event
        spinner_name.setAdapter(dataAdapter);

        spinner_name.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int pos,long id) {
                String item_selected = parent.getItemAtPosition(pos).toString();
                /*Toast.makeText(parent.getContext(), "OnItemSelectedListener : " + parent.getItemAtPosition(pos).toString(),
                        Toast.LENGTH_SHORT).show();*/
                competitor_selected = item_selected;
            }

            @Override
            public void onNothingSelected(AdapterView<?> arg0) {
                // TODO Auto-generated method stub
            }
        });
    }

    public static boolean checkTimeFormat(String time){
        if(time.equals("DNF") || time.equals("dnf")){
            return true;
        }
        else if(time.equals("DNS") || time.equals("dns")){
            return true;
        }
        int length = time.length();
        if(length != 7){
            return false;
        }
        char c = ':';
        char c2 = '.';
        if(time.charAt(1) == c && time.charAt(4) == c2){
            String[] s = time.split(":");
            String[] s2 = s[1].split("\\.");
            if(!isInteger(s[0])){
                return false;
            }
            else if(!isInteger(s2[0])){
                return false;
            }
            else if(!isInteger2(s2[1])){
                return false;
            }
            else{
                return true;
            }
        }
        else {
            return false;
        }
    }

    public static boolean isInteger(String s) {
        try {
            int test = Integer.parseInt(s);
            if(test < 0 || test >=60){
                return false;
            }
        } catch(NumberFormatException e) {
            return false;
        } catch(NullPointerException e) {
            return false;
        }
        // only got here if we didn't return false
        return true;
    }

    public static boolean isInteger2(String s) {
        try {
            int test = Integer.parseInt(s);
            if(test < 0 || test >=100){
                return false;
            }
        } catch(NumberFormatException e) {
            return false;
        } catch(NullPointerException e) {
            return false;
        }
        // only got here if we didn't return false
        return true;
    }

    public static boolean isLimitReach(String limit, String t1){
        limit = limit.substring(0,4) + ":" + limit.substring(5);
        limit = "0" + limit;
        String[] s = limit.split(":");
        String[] s2 = t1.split(":");
        String[] s4 = s2[1].split("\\.");

        int cutoff = Integer.parseInt(s[0])*3600+Integer.parseInt(s[1])*60+Integer.parseInt(s[2]);
        int time_comp = Integer.parseInt(s2[0])*3600+Integer.parseInt(s4[0])*60+Integer.parseInt(s4[1]);
        if(cutoff<=time_comp){
            return true;
        }
        else {
            return false;
        }
    }

    public static String removeZero(String str){
        char c = '0';
        if(str.equals("00")){
            return "0";
        }
        else if(str.charAt(0) == c){
            return "" + str.charAt(1);
        }
        else{
            return str;
        }
    }

    @Override
    public void onClick(View view) {
        if(currentTime == times+1 || limitTimeReach){
            int new_times = times;
            if(limitTimeReach && times == 5){
                new_times = 2;
            }
            else if(limitTimeReach && times == 3){
                new_times = 1;
            }
            final String[] timesTab = new String[5];
            for(int i=0;i<new_times;i++){
                timesTab[i] = "0" + editTexts[i].getText().toString();
                timesTab[i] = timesTab[i].substring(0,5) + ":" + timesTab[i].substring(6);
                //Toast.makeText(getApplicationContext(), timesTab[i] , Toast.LENGTH_SHORT).show();
            }
            for(int i=new_times;i<5;i++){
                timesTab[i] = "";
            }
            request = new StringRequest(Request.Method.POST, URL_times, new Response.Listener<String>() {
                @Override
                public void onResponse(String response) {
                    try {
                        JSONObject jsonObject = new JSONObject(response);
                        String result = jsonObject.getString("success");
                        Toast.makeText(getApplicationContext(), result , Toast.LENGTH_SHORT).show();

                        /*finish();
                        startActivity(getIntent());*/

                        for(int i = 0; i<times; i++){
                            editTexts[i].setText("");
                            checkBoxes[i].setChecked(false);
                        }
                        for(int i = 1; i<times; i++){
                            relativeLayouts[i].setEnabled(false);
                        }
                        spinner_event.setEnabled(true);
                        currentTime = 1;
                        limitTimeReach = false;
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
                protected Map<String, String> getParams() throws AuthFailureError {
                    HashMap<String, String> hashMap = new HashMap<String, String>();
                    hashMap.put("login", login);
                    hashMap.put("password", password);
                    hashMap.put("comp", competitor_selected);
                    hashMap.put("event", event_selected);
                    hashMap.put("round", round);
                    hashMap.put("format", String.valueOf(times));
                    hashMap.put("t1", timesTab[0]);
                    hashMap.put("t2", timesTab[1]);
                    hashMap.put("t3", timesTab[2]);
                    hashMap.put("t4", timesTab[3]);
                    hashMap.put("t5", timesTab[4]);

                    return hashMap;
                }
            };

            requestQueue.add(request);
        }
        else{
            Toast.makeText(this, "You must check all checkboxes", Toast.LENGTH_SHORT).show();
        }
    }

    public EditText getCurrentTextBox(){
        if(currentTime <= times){
            return editTexts[currentTime-1];
        }
        else{
            return null;
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.main_menu_items, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();
        if (id == R.id.actionLogout) {
            // Erase login and password in Preferences
            PreferenceUtils.setLogin(null);
            PreferenceUtils.setPassword(null);

            // Finish this activity, and go back to LoginActivity
            finish();

            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    @Override
    public void onPause(){
        super.onPause();
        if (sat!=null) {
            sat.cancel(true);
        }
    }


    @Override
    public void onDestroy()
    {
        super.onDestroy();

        sat.acti = null;

        if (this.isFinishing())
            sat.cancel(false);
    }

}
