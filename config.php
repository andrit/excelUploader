<?php 

return array(

	"rootdir" => "bulkpricing",

    "appdir" => dirname(__FILE__) . "/app",

    "errorreporting" 	=> 0, // levels = E_ALL or -1 for all errors, 0 turn off all error reporting

    "displayerrors" 	=> "On", // either On or Off

    "restrictbyip"		=> "On",

    "allowediplist"		=> array("10.2", "10.22.2"),

    "pdocloc"			=> "000000015726466",

    "streetname"		=> array(

    	"avenue"		=> array("av","ave","aven","avn","av.","ave.","aven.","avn."),

    	"boulevard"			=> array("bld","blv","blvd","boul","boulv","boulvd","bld.","blv.","blvd.","boul.","boulevard.","boulv.","boulvd."),

    	"drive"			=> array("dr","driv","drv","dr.","driv.","drv."),

    	"court"			=> array("crt","ct","crt.","ct."),

    	"highway"		=> array("hw","highwy","hiway","hiwy","hway","hwy","highwy.","hiway.","hiwy.","hway.","hwy.","highw","highw.","hw."),

    	"junction"		=> array("jct","jction","jctn","junc","junct","junctn","juncton","jct.","jction.","jctn.","junctn.","juncton.","junc.","junct."),

    	"lane"			=> array("ln","ln.","lan","lan."),

    	"parkway"		=> array("parkwy","prkwy","pkway","pkwy","pky","pkw","parkwy.","pkway.","pkwy.","pky.","pkw.","prkwy."),

    	"road"			=> array("rd","rd."),

    	"roads"			=> array("rds","rds."),

    	"square"		=> array("sq","sqr","sqre","squ","sq.","sqr.","sqre.","squ."),

    	"street"		=> array("str","st","strt","str.","st.","strt."),

    	"turnpike"		=> array("trnp","turnp","trpk","tpk","trnpk","turnpk","trnpk.","turnpk.","turnp.","trpk.","tpk."),

    	"way"			=> array("wy","wy.")

    ),

    "streetdirection" 	=> array(

    	"north"			=> array("n","nth","nrt","nrth","n.","nth.","nrt.","nrth."),

    	"east"			=> array("e","es","est","e.","es.","est."),

    	"west"			=> array("w","wst","w.","wst."),

    	"south"			=> array("s","s.","st","st.","sth","sth.","sh","sh.")

    )

);

// pdocloc is not the actual value
// value here entered for testing purposes
// pdocloc will come from GENRFP file when SOEWAB - DESLOC - GENRFG programs are called 
// under the customerInfo sub-procedure in CGTVAP program.