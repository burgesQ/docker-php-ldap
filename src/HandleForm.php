<table>
    <tr>
        <th>Field Name</th>
        <th>Value(s)</th>
    </tr>

    <?php

    include("LDAPHelper.php");

    if (empty($_POST)) {
        print "<p>No data was submitted.</p>";
    } else {

        foreach ($_POST as $key => $value) {

            if (get_magic_quotes_gpc())
                $value = stripslashes($value);

            if ($key !== "submit")
                print "<tr><td><code>$key</code></td><td><i>$value</i></td></tr>\n";
        }

        $host       = $_POST['host'];
        $dn         = $_POST['ldap_dn'];
        $user       = $_POST['username'];
        $password   = $_POST['password'];
        $extraGroup = $_POST['group'];

        error_log("\n\nCa try!\n", 3, "/var/tmp/my-errors.log");

        $ldap = new LDAPHelper($host, $dn);

        if ($ldap) {
            $retVal = $ldap->authUserOverLDAP($dn, $user, $password, $extraGroup);
            print "<tr><td><code>Is logged</code></td><td><i>{$retVal["reason"]}</i></td></tr>\n";

            if ($retVal["logged"])
                foreach ($retVal["userGroups"] as $oneRole)
                    print "<tr><td><code>Object Class</code></td><td><i>{$oneRole}</i></td></tr>\n";

        }
    }

    ?>

</table>
