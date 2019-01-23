<?php
namespace Wink\DB;

use Wink\Service\Time;

class Dao {
    /** @var \PDO */
    protected $pdo;

    /**
     * Dao constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct ($pdo) {
        $this->setPdo($pdo);
    }

    /**
     * @return array|false Array with attributes on success, false on failure
     */
    public function getDevices () {
        $stmt = $this->pdo->prepare("
            SELECT
                d.device_id,
                d.mac_address,
                d.orientation,
                d.batteries_replaced_date,
                d.source_plugin,
                d.layout_type,
                d.notes,
                d.width,
                d.height,
                d.is_production,
                ci.check_in_dt,
                ci.next_check_in_dt,
                ci.voltage,
                ci.firmware_version,
                ci.error_code,
                ci.remote_address
            FROM device d
            LEFT JOIN check_in ci ON d.check_in_id = ci.check_in_id
        ");

        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $rows = array_map(function ($row) {
            $row['batteries_replaced_date'] = Time::fromUTCToLocal($row['batteries_replaced_date']);
            $row['check_in_dt'] = Time::fromUTCToLocal($row['check_in_dt']);
            $row['next_check_in_dt'] = Time::fromUTCToLocal($row['next_check_in_dt']);
            return $row;
        }, $rows);

        return $rows;
    }

    /**
     * @param string $id 12 character MAC Address (no :s please) or the device ID
     * @return array|false Array with attributes on success, false on failure
     */
    public function getDevice ($id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                d.device_id,
                d.mac_address,
                d.orientation,
                d.batteries_replaced_date,
                d.source_plugin,
                d.source_options,
                d.layout_type,
                d.layout_options,
                d.notes,
                d.width,
                d.height,
                d.is_production
            FROM device d
            WHERE mac_address = ? OR device_id = ?
            LIMIT 1
        ");

        $stmt->execute([$id, $id]);

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $result['batteries_replaced_date'] = Time::fromUTCToLocal($result['batteries_replaced_date']);
            $result['layout_options'] = json_decode($result['layout_options'], true);
            $result['source_options'] = json_decode($result['source_options'], true);

            return $result;
        }

        return false;
    }

    /**
     * @param string|int $deviceId
     * @return bool|mixed
     */
    public function getDeviceHistory ($deviceId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                ci.check_in_dt,
                ci.next_check_in_dt,
                ci.voltage,
                ci.firmware_version,
                ci.error_code,
                ci.remote_address
            FROM check_in ci
            INNER JOIN device d ON ci.device_id = d.device_id
            WHERE d.mac_address = ? OR d.device_id = ?
            ORDER BY ci.check_in_dt DESC
        ");

        $stmt->execute([$deviceId, $deviceId]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $rows = array_map(function ($row) {
            $row['check_in_dt'] = Time::fromUTCToLocal($row['check_in_dt']);
            $row['next_check_in_dt'] = Time::fromUTCToLocal($row['next_check_in_dt']);

            return $row;
        }, $rows);

        return $rows;
    }

    /**
     * @param string $id 12 character MAC Address (no :s please) or the device ID
     * @param array $device
     * @return array|false
     */
    public function updateDevice ($id, $device) {
        $stmt = $this->pdo->prepare("
            UPDATE device
            SET 
                mac_address = :mac_address,
                orientation = :orientation,
                batteries_replaced_date = :batteries_replaced_date,
                source_plugin = :source_plugin,
                source_options = :source_options,
                layout_type = :layout_type,
                layout_options = :layout_options,
                notes = :notes,
                width = :width,
                height = :height,
                is_production = :is_production
            WHERE device_id = :device_id
            LIMIT 1
        ");

        $device['device_id'] = $id;
        $device['batteries_replaced_date'] = Time::fromLocalToUTC($device['batteries_replaced_date']);
        $device['layout_options'] = json_encode($device['layout_options']);
        $device['source_options'] = json_encode($device['source_options']);

        $stmt->execute($device);

        return $this->getDevice($id);
    }

    public function deleteDevice ($deviceId) {
        $stmt = $this->pdo->prepare("DELETE FROM device WHERE device_id = :device_id");

        return $stmt->execute(['device_id' => $deviceId]);
    }

    public function createDevice ($macAddress, $width, $height) {
        $stmt = $this->pdo->prepare("
            INSERT INTO device (mac_address, width, height, batteries_replaced_date)
            VALUES (:mac_address, :width, :height, :batteries_replaced_date)
        ");

        $stmt->execute([
            'mac_address' => $macAddress,
            'width' => $width,
            'height' => $height,
            'batteries_replaced_date' => Time::fromLocalToUTC(Time::getCurrentDateTime())
        ]);

        return $this->getDevice($macAddress);
    }

    public function insertCheckIn ($macAddress, $nextCheckIn, $voltage, $firmwareVersion, $errorCode, $remoteAddress) {
        $stmt = $this->pdo->prepare("
            INSERT INTO check_in 
            (
                device_id, 
                check_in_dt,
                next_check_in_dt, 
                voltage, 
                firmware_version, 
                error_code,
                remote_address
            )
            SELECT 
                d.device_id, 
                :check_in_dt,
                :next_check_in_dt, 
                :voltage, 
                :firmware_version, 
                :error_code,
                :remote_address
            FROM device d 
            WHERE d.mac_address = :mac_address
        ");

        $stmt->execute([
            'mac_address' => $macAddress,
            'check_in_dt' => Time::fromLocalToUTC(Time::getCurrentDateTime()),
            'next_check_in_dt' => Time::fromLocalToUTC($nextCheckIn),
            'voltage' => $voltage,
            'firmware_version' => $firmwareVersion,
            'error_code' => $errorCode,
            'remote_address' => $remoteAddress,
        ]);

        $checkInId = $this->pdo->lastInsertId();

        $deviceStmt = $this->pdo->prepare("
            UPDATE device
            SET check_in_id = :check_in_id
            WHERE mac_address = :mac_address
        ");

        $deviceStmt->execute([
            'mac_address' => $macAddress,
            'check_in_id' => $checkInId
        ]);
    }

    /**
     * @return \PDO
     */
    public function getPdo () {
        return $this->pdo;
    }

    /**
     * @param \PDO $pdo
     */
    public function setPdo ($pdo) {
        $this->pdo = $pdo;
    }
}