<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 16-3-2017
 * Time: 15:22
 */
class chats {
    static function loadChats($mysqli, $user) {
        $sql="SELECT * FROM pco_chats WHERE user1='$user' OR user2='$user' ORDER BY last_activity DESC";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                if(chats::checkIfChatHasNewMessages($mysqli, $row['ID'], $user)) {
                    if (strcmp($row['user1'], $user) == 0) {
                        echo '<div class="hover" onclick="openChat(' . $row['ID'] . ')"><img onclick="openUserPage(' . user::getIDFromUUID($mysqli, $row['user2']) . ')" class="avatar pull-left hover" src="resources/icon.png" alt="" style="display: block; margin: 0 auto; margin-right:5px;"/>
                        <span><strong>' . user::getNameByUUID($mysqli, $row['user2']) . '</strong></span><br /><span class="text-muted">' . chats::getLastMessage($mysqli, $row['ID']) . '</span><strong><span class="pull-right text-success">Nieuw bericht!</span></strong>
                        </div><hr />';
                    } else if (strcmp($row['user2'], $user) == 0) {
                        echo '<div class="hover" onclick="openChat(' . $row['ID'] . ')"><img onclick="openUserPage(' . user::getIDFromUUID($mysqli, $row['user1']) . ')" class="avatar pull-left hover" src="resources/icon.png" alt="" style="display: block; margin: 0 auto; margin-right:5px;"/>
                        <span><strong>' . user::getNameByUUID($mysqli, $row['user1']) . '</strong></span><br /><span class="text-muted">' . chats::getLastMessage($mysqli, $row['ID']) . '</span><strong><span class="pull-right text-success">Nieuw bericht!</span></strong>
                        </div><hr />';
                    }
                } else {
                    if (strcmp($row['user1'], $user) == 0) {
                        echo '<div class="hover" onclick="openChat(' . $row['ID'] . ')"><img onclick="openUserPage(' . user::getIDFromUUID($mysqli, $row['user2']) . ')" class="avatar pull-left hover" src="resources/icon.png" alt="" style="display: block; margin: 0 auto; margin-right:5px;"/>
                        <span><strong>' . user::getNameByUUID($mysqli, $row['user2']) . '</strong></span><br /><span class="text-muted">' . chats::getLastMessage($mysqli, $row['ID']) . '</span>
                        </div><hr />';
                    } else if (strcmp($row['user2'], $user) == 0) {
                        echo '<div class="hover" onclick="openChat(' . $row['ID'] . ')"><img onclick="openUserPage(' . user::getIDFromUUID($mysqli, $row['user1']) . ')" class="avatar pull-left hover" src="resources/icon.png" alt="" style="display: block; margin: 0 auto; margin-right:5px;"/>
                        <span><strong>' . user::getNameByUUID($mysqli, $row['user1']) . '</strong></span><br /><span class="text-muted">' . chats::getLastMessage($mysqli, $row['ID']) . '</span>
                        </div><hr />';
                    }
                }
            }
        } else {
            echo 'Nog geen chats verstuurd of ontvangen!';
        }
    }
    static function getLastMessage($mysqli, $chatid) {
        if(!chats::isChatOfUser($mysqli, $chatid, $_SESSION['UUID'])) {
            echo 'error';
        }
        $sql="SELECT * FROM pco_chats_messages WHERE chat_id='$chatid' ORDER BY ID DESC";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            $string = '';
            if(strlen($row['message']) > 50) {
                $string = '.....';
            }
            return user::getNameByUUID($mysqli, $row['sender']).": ".substr($row['message'], 0, 50).$string;
        } else {
            return 'Nog geen berichten verstuurd.';
        }
    }
    static function loadChat($mysqli, $chatid) {
        if(!chats::isChatOfUser($mysqli, $chatid, $_SESSION['UUID'])) {
            echo 'error';
        }
        $sql="SELECT * FROM pco_chats_messages WHERE chat_id='$chatid' ORDER BY chat_id ASC";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            echo '<input type="hidden" id="id" value="'.$chatid.'"/>';
            echo $chatid;
            while($row = mysqli_fetch_assoc($result)) {
                echo '<div>
                        <img class="avatar pull-left hover" src="resources/icon.png" alt="" style="display: block; margin: 0 auto; margin-right:5px;"/>
                        <span><strong>' . user::getNameByUUID($mysqli, $row['sender']) . '</strong></span><br />
                        <span class="">'.$row['message'].'</span><br /><span class="text-muted">'.$row['sended'].'</span>
                </div><hr />';
            }
        } else {
            echo '<input type="hidden" id="id" value="'.$chatid.'"/>';
        }
    }
    static function loadChatReload($mysqli, $chatid) {
        if(!chats::isChatOfUser($mysqli, $chatid, $_SESSION['UUID'])) {
            echo 'error';
        }
        $sql1="UPDATE pco_chats_messages SET readed='1' WHERE chat_id='$chatid' AND readed='0' AND sender NOT IN ('".$_SESSION['UUID']."')";
        $result1 = mysqli_query($mysqli, $sql1);

        $sql="SELECT * FROM pco_chats_messages WHERE chat_id='$chatid' ORDER BY chat_id ASC";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            echo '<input type="hidden" id="id" value="'.$chatid.'"/>';
            echo $chatid;
            while($row = mysqli_fetch_assoc($result)) {
                echo '<div>
                        <img class="avatar pull-left hover" src="resources/icon.png" alt="" style="display: block; margin: 0 auto; margin-right:5px;"/>
                        <span><strong>' . user::getNameByUUID($mysqli, $row['sender']) . '</strong></span><br />
                        <span class="">'.$row['message'].'</span><br /><span class="text-muted">'.$row['sended'].'</span>
                </div><hr />';
            }
        } else {
            echo '<input type="hidden" id="id" value="'.$chatid.'"/>';
        }
    }
    static function sendMessage($mysqli, $chatid, $message, $user) {
        if(!chats::isChatOfUser($mysqli, $chatid, $user)) {
            return;
        }
        if(strcmp($message, '') == 0) {
            return;
        }
        $sql="INSERT INTO pco_chats_messages (chat_id, sender, message, sended) VALUES ('$chatid', '".$user."', '$message', '".strftime('%e-%m-%Y om %H:%M', time())."')";
        $result = mysqli_query($mysqli, $sql);
        $sql="UPDATE pco_chats SET last_activity=CURRENT_TIMESTAMP() WHERE ID='$chatid'";
        $result = mysqli_query($mysqli, $sql);
    }
    static function getNameOfChatter($mysqli, $chatid) {
        if(!chats::isChatOfUser($mysqli, $chatid, $_SESSION['UUID'])) {
            return 'error';
        }
        $sql="SELECT * FROM pco_chats WHERE ID='$chatid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            $row = mysqli_fetch_assoc($result);
            if(strcmp($row['user1'], $_SESSION['UUID']) == 0) {
                return user::getNameByUUID($mysqli, $row['user2']);
            }
            if(strcmp($row['user2'], $_SESSION['UUID']) == 0) {
                return user::getNameByUUID($mysqli, $row['user1']);
            }
            return 'error';
        }
    }
    static function isChatOfUser($mysqli, $chatid, $userid) {
        $sql="SELECT * FROM pco_chats WHERE ID='$chatid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            if(strcmp($userid, $row['user1']) == 0) {
                return true;
            } else if(strcmp($userid, $row['user2']) == 0) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
    static function startChat($mysqli, $user1, $user2) {
        $sql="SELECT * FROM pco_chats WHERE user1='$user1' AND user2='$user2' OR user1='$user2' AND user2='$user1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count == 0) {
            $sql="INSERT INTO pco_chats (user1, user2, last_activity) VALUES ('$user1', '$user2', CURRENT_TIMESTAMP())";
            $result = mysqli_query($mysqli, $sql);
        }
    }
    static function getChatID($mysqli, $user1, $user2) {
        $sql="SELECT * FROM pco_chats WHERE user1='$user1' AND user2='$user2' OR user1='$user2' AND user2='$user1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['ID'];
        }
    }
    static function countNotReadedMessages($mysqli, $userid) {
        $counter = 0;
        $sql="SELECT * FROM pco_chats_messages WHERE readed='0' AND sender NOT IN ('$userid')";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)) {
            $chatid = $row['chat_id'];
            $sql1="SELECT * FROM pco_chats WHERE ID='$chatid'";
            $result1 = mysqli_query($mysqli, $sql1);
            $count1 = mysqli_num_rows($result1);
            $row1 = mysqli_fetch_assoc($result1);
            if($count1 > 0) {
                if(strcmp($userid, $row1['user1']) == 0) {
                    $counter++;
                } else if(strcmp($userid, $row1['user2']) == 0) {
                    $counter++;
                }
            }
        }
        return $counter;
    }
    static function checkIfChatHasNewMessages($mysqli, $chatid, $userid) {
        $newmessages = false;
        $sql="SELECT * FROM pco_chats_messages WHERE readed='0' AND sender NOT IN ('$userid') AND chat_id='$chatid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)) {
            $newmessages = true;
        }
        return $newmessages;
    }
}