<?php 
interface iCrud {
    public function create($data);
    public function read();
    public function update($data);
    public function delete();  
}


class User implements iCrud {    
    private $_userId = NULL;
    private $_username = NULL;

    function __construct($data) {
        $this->_userId = uniqid();
        $this->_username = $data['username'];
    }

    function create($data) {
        self::__construct($data);
    }
    
    function read() {
        return array('userId' => $this->_userId, 'username' => $this->_username);
    } 

    function update($data) {
        $this->_username = $data['username'];
    }

    public function delete() {
        $this->_username = NULL;
        $this->_userId = NULL;
    }    
}

//Korisničko ime
$user = array('username' => 'djuro');
echo "<h2>Stvaranje novog korisnika</h2>";
$me = new User($user);
// ID korisnika
$info = $me->read();
echo "<p>ID je {$info['userId']}.</p>";
echo "<p>Korisničko ime je {$info['username']}.</p>";

// Promijeni korisničko ime
$me->update(array('username' => 'pero'));

//Potvrdi novo korisničko ime
$info = $me->read();
echo "<p>Novo korisničko ime je {$info['username']}.</p>";

//Obriši korisnika
$me->delete(); 
    
//Obriši objekt:
unset($me);

?>

