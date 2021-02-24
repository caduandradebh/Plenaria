<?php

/**
 * Classe de gerenciamento de banco de dados.
 * Monta todas as querys do db.
 * @author Cadu
 */
class SiteFuncoes extends DbSiteCon {

    protected $db;

    /**
     * Função para cadastar dados no DB
     * Retorna o número de linhas afetadas.
     * @param string $tabela
     * @param array $campos
     * @param array $valores
     * @return int $numRows
     */
    public function insert($tabela, $campos, $valores) {
        $this->conexao();
        $campo = implode(", ", $campos);
        $valor = implode(", ", $valores);
        $query = "INSERT INTO $tabela ($campo) VALUES ($valor)";
        //var_dump($query);
        try {
            $numRows = $this->db->exec($query);
        } catch (PDOException $ex) {
            if ($_SESSION['userId'] == 1 && $_SESSION['userName'] == "eduardo") {
                print $this->eventoErroDB(
                                $tabela . "->" . __FUNCTION__, $query, date("d/m/Y H:i:s"), $ex->getMessage()
                        );
            }
            $numRows = FALSE;
        }
        $_SESSION['ultId'] = $this->buscaId();
        $this->closeConnection();
        return $numRows;
    }

    /**
     * Função criada para atualizar dados no DB
     * Retorna o número de linhas afetadas.
     * @param string $tabela
     * @param array $valores
     * @param array $condicao
     * @return int $numRows
     */
    public function update($tabela, $valores, $condicao) {
        $this->conexao();
        $where = implode(", ", $condicao);
        $valor = implode(", ", $valores);
        $query = "UPDATE $tabela SET $valor WHERE $where";
        try {
            $numRows = $this->db->exec($query);
        } catch (PDOException $ex) {
            if ($_SESSION['userId'] == 1 && $_SESSION['userName'] == "eduardo") {
                print $this->eventoErroDB(
                                $tabela . "->" . __FUNCTION__, $query, date("d/m/Y H:i:s"), $ex->getMessage()
                        );
            }
            $numRows = FALSE;
        }
        $this->closeConnection();
        return $numRows;
    }

    /**
     * Função criada para deletar dados no DB.
     * Retorna o número de linhas afetadas.
     * @param string $tabela
     * @param array $condicao
     * @return int $numRows
     */
    public function delete($tabela, $condicao) {
        $this->conexao();
        $where = implode(", ", $condicao);
        $query = "DELETE FROM $tabela WHERE $where";
        try {
            $numRows = $this->db->exec($query);
        } catch (PDOException $ex) {
            if ($_SESSION['userId'] == 1 && $_SESSION['userName'] == "eduardo") {
                print $this->eventoErroDB(
                                $tabela . "->" . __FUNCTION__, $query, date("d/m/Y H:i:s"), $ex->getMessage()
                        );
            }
            $numRows = FALSE;
        }
        $this->closeConnection();
        return $numRows;
    }

    /**
     * $tabelas agora pode ser um campo string ou um array.
     * 
     * Função criada para buscar algum dado no banco de dados.
     * Todos os parametros são do tipo array.
     * $limite tem que ser um parametro de 2 posições, pos. 0 e pos. 1.
     * 
     * @param string ou array $tabelas
     * @param array $campos
     * @param array $condicao
     * @param array $join
     * @param array $ordem
     * @param array[2] $limite
     * @return Resource $result
     */
    public function select($tabelas, $campos = NULL, $condicao = NULL, $join = NULL, $ordem = NULL, $limite = NULL) {
        // Abre a conexão com o Banco de Dados.
        $this->conexao();
        // Define as tabelas a serem buscadas.
        $tabela = (is_array($tabelas) ? implode(", ", $tabelas) : $tabelas);
        // Define os campos.
        $campo = ($campos != NULL ? implode(", ", $campos) : "*");
        // Define a minha condição.
        $where = ($condicao != NULL && is_array($condicao)) ? implode(" AND ", $condicao) : NULL;
        // Define a ligação entre tais tabelas.
        $joins = ($join != NULL && is_array($join)) ? implode(" ", $join) : NULL;
        // Define a ordem em que meus dados irão aparecer.
        $order = ($ordem != NULL && is_array($ordem)) ? implode(", ", $ordem) : NULL;
        // Define o limite de dados.
        $limite = ($limite != NULL && is_array($limite) && count($limite) == 2) ? " LIMIT " . $limite[0] . ", " . $limite[1] : NULL;
        // Monta a query.
        $query = "SELECT $campo FROM $tabela";
        if ($joins != NULL) {
            $query .= " $joins ";
        }
        if ($where != NULL) {
            $query .= " WHERE $where";
        }
        if ($order != NULL) {
            $query .= " ORDER BY $order";
        }
        if ($limite != NULL) {
            $query .= $limite;
        }
       //var_dump($query);
        try {
            // Executa a query.
            $stmt = $this->db->query($query);
            // seta o modo Fetch.
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            // atribui a variavel result o resultado da busca.
            $result = $stmt->fetchall();
            $stmt->closeCursor();
        } catch (PDOException $ex) {
            //HABILITAR PARA VER ERROS -> if ($_SESSION['userId'] == 1 && $_SESSION['userName'] == "eduardo") {
            if (1 == 1) {
                print $this->eventoErroDB(
                                $tabela . "->" . __FUNCTION__, $query, date("d/m/Y H:i:s"), $ex->getMessage()
                        );
            }
            $result = FALSE;
        }
        // Fecha a conexão com o banco de dados.
        $this->closeConnection();
        return $result;
    }

    /**
     * Função utilizada para buscar a última id cadastrada no BD.
     * @return MIXED VALUE
     */
    public function buscaId() {
        $result = FALSE;
        try {
            $result = $this->db->lastInsertId();
        } catch (PDOException $exc) {
            if ($_SESSION['userId'] == 1 && $_SESSION['userName'] == "eduardo") {
                print $this->eventoErroDB(
                                __CLASS__ . "->" . __FUNCTION__, 'Sem Query', date("d/m/Y H:i:s"), $exc->getMessage()
                        );
            }
            $result = FALSE;
        }
        return $result;
    }

    /**
     * Função para pegar o Ip de quem visitar o site.
     * @author Cadu
     */
    function getIp() {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Função que gera o log e cadastra o mesmo no banco de dados
     * UPDATE: 
     * @author Cadu
     * @param $acao[]
     * @param $login
     * @param $arrayLogin[]
     * @return $numRows
     */
    public function registrarLog($acao, $login = FALSE, $arrayLogin = null) {
        if (!is_array($acao)) {
            $acao = array();
        }
        // Crio um array $erro para mostrar no final se deu ou não algum erro,
        // relacionado com os campos de Id, Nome e Login do Operador.
        $erro = array();

        // Seto os campos a serem inseridos no bd.
        $arrayCampos = array(
            "logsUserId",
            "logsUserName",
            "logsLogin",
            "logsIpExterno",
            "logsIpInterno",
            "logsHostName"
        );
        // Verifico se o operador está logando agora.
        if (!$login) {
            // Se ele já estiver logado, monto meu array de valores com a session, checando se os campos não estão vazios.
            // Se estiverem, o array $erro receberá uma msg.
            $valores = array(
                ((isset($_SESSION['userId']) && !empty($_SESSION['userId'])) ? "'" . $_SESSION['userId'] . "'" : $erro[0] = "UserId vazio"),
                ((isset($_SESSION['operNome']) && !empty($_SESSION['operNome'])) ? "'" . $_SESSION['operNome'] . "'" : $erro[1] = "UserName vazio"),
                ((isset($_SESSION['userName']) && !empty($_SESSION['userName'])) ? "'" . $_SESSION['userName'] . "'" : $erro[2] = "UserLogin vazio"),
                "'" . $this->getIp() . "'",
                "'" . $this->getIp() . "'",
                "'" . gethostbyaddr($this->getIp()) . "'"
            );
        } else {
            // Se vier da página de login, onde ainda não há uma $_SESSION definida,
            // uso o arrayLogin, passado como parametro para montar os meus valores.
            $valores = array(
                ((isset($arrayLogin['userId']) && !empty($arrayLogin['userId'])) ? "'" . $arrayLogin['userId'] . "'" : $erro[0] = "UserId vazio"),
                ((isset($arrayLogin['operNome']) && !empty($arrayLogin['operNome'])) ? "'" . $arrayLogin['operNome'] . "'" : $erro[1] = "UserName vazio"),
                ((isset($arrayLogin['userName']) && !empty($arrayLogin['userName'])) ? "'" . $arrayLogin['userName'] . "'" : $erro[2] = "UserLogin vazio"),
                "'" . $this->getIp() . "'",
                "'" . $this->getIp() . "'",
                "'" . gethostbyaddr($this->getIp()) . "'"
            );
        }
        // Adiciono ao meu array campo os campos da Ação e ao array
        // valores os seus respectivos valores.
        foreach ($acao as $key => $value) {
            $arrayCampos[] = $key;
            $valores[] = "'" . $value . "'";
        }
        // Verifico se não há nenhum erro.
        if (empty($erro)) {
            // Se não houver, verifico se meus campos não estão vazios.
            if (!empty($arrayCampos)) {
                // Retorno o número de linhas afetadas no meu banco de dados após fazer a inserção de dados no mesmo.
                return $this->insert("logs", $arrayCampos, $valores);
            } else {
                // Se o arrayCampo estiver vazio, mostra uma msg de erro.
                print "arrayCampos vazio!";
                return FALSE;
            }
        } else {
            // Caso o meu array de erro não esteja vazio, 
            // printo na tela todos os erros.
            foreach ($erro as $value) {
                print $value . "<br />";
                return FALSE;
            }
        }
    }
	
	//CONSULTA SIMPLES
        function BuscaDadosSite($tabnome,$tabcampo,$tabvalor,$tabexibe){ 
            $this->conexao();
            $sql = "SELECT $tabexibe FROM $tabnome WHERE $tabcampo='$tabvalor' ";
            // Executa a query.
        try {

        // Executa a query.
            $stmt = $this->db->query($sql);
            // seta o modo Fetch.
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            // atribui a variavel result o resultado da busca.
            $result = $stmt->fetchall();

            foreach ($result as $row) {
                $res =  $row->$tabexibe;
            }

            $stmt->closeCursor();

            
        }catch (PDOException $ex) {
            //HABILITAR PARA VER ERROS -> if ($_SESSION['userId'] == 1 && $_SESSION['userName'] == "eduardo") {
            if (1 == 1) {
                print $this->eventoErroDB(
                                $tabnome . "->" . __FUNCTION__, $sql, date("d/m/Y H:i:s"), $ex->getMessage()
                        );
            }
            $res = FALSE;
        }
            $this->closeConnection();
            return $res;  
        }


}

?>