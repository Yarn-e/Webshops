<?php
//MYBA
function checkEmailKey($key,$uname)
{
    global $link;
    if ($SQL = $link->prepare("SELECT `IDRec` FROM `UserRecovery`
                               WHERE `recKey` = ? AND `IDUser` = ?"))
    {
        $SQL->bind_param('ss',$key,$uname);
        $SQL->execute();
        $SQL->store_result();
        $numRows = $SQL->num_rows();
        $SQL->bind_result($ID);
        $SQL->fetch();
        $SQL->close();
        if ($numRows > 0 && $ID != '')
        {
            return array('status'=>true,'userID'=>$ID);
        }
    }
    return array('status'=>false,'userID'=>0);
}