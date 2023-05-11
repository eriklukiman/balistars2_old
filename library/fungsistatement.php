<?php

function selectStatement($db, $query, $parameter = [], $type = 'fetchAll')
{

    try {
        $sqlStatement = $db->prepare($query);
        $res = $sqlStatement->execute($parameter);

        if (!$res) {
            $error = $sqlStatement->errorInfo();
            $errorStatement = $error[2];
            throw new PDOException($errorStatement);
        } else {
            if ($type == 'fetchAll') {
                $data = $sqlStatement->fetchAll();
            } else if ($type == 'fetch') {
                $data = $sqlStatement->fetch();
            }

            return $data;
        }
    } catch (PDOException $e) {
?>
        <div class="alert alert-danger" role="alert">
            SQL ERROR : <strong><?= $e->getMessage(); ?></strong>
        </div>
    <?php
    }
}

function insertStatement($db, $query, $parameter = [])
{
    try {
        $sqlStatement = $db->prepare($query);
        $res = $sqlStatement->execute($parameter);

        if (!$res) {
            $error = $sqlStatement->errorInfo();
            $errorStatement = $error[2];
            throw new PDOException($errorStatement);
        } else {
            return $res;
        }
    } catch (PDOException $e) {
    ?>
        <div class="alert alert-danger" role="alert">
            SQL INSERT ERROR : <strong><?= $e->getMessage(); ?></strong>
        </div>
    <?php
    }
}

function updateStatement($db, $query, $parameter = [])
{
    try {
        $sqlStatement = $db->prepare($query);
        $res = $sqlStatement->execute($parameter);

        if (!$res) {
            $error = $sqlStatement->errorInfo();
            $errorStatement = $error[2];
            throw new PDOException($errorStatement);
        } else {
            return $res;
        }
    } catch (PDOException $e) {
    ?>
        <div class="alert alert-danger" role="alert">
            SQL UPDATE ERROR : <strong><?= $e->getMessage(); ?></strong>
        </div>
    <?php
    }
}

function deleteStatement($db, $query, $parameter = [])
{
    try {
        $sqlStatement = $db->prepare($query);
        $res = $sqlStatement->execute($parameter);

        if (!$res) {
            $error = $sqlStatement->errorInfo();
            $errorStatement = $error[2];
            throw new PDOException($errorStatement);
        } else {
            return $res;
        }
    } catch (PDOException $e) {
    ?>
        <div class="alert alert-danger" role="alert">
            SQL DELETE ERROR : <strong><?= $e->getMessage(); ?></strong>
        </div>
<?php
    }
}
