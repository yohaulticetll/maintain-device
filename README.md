PS/Maintain Device
========================

Simple REST API allowing to flag devices according to current processing state.

# Installation
```
git clone https://github.com/yohaulticetll/maintain-device.git
cd maintain-device/
composer update
```
## Running Tests

```
composer test
```

## Endpoints

***Add Device***

  Creates a device

* **URL**

  /api/devices

* **Method:**

  `POST` 

* **Data Params**

  **Required:** `{"serialNumber":"SN560"}`

* **Success Response:**

  * **Code:** 201 <br />
    **Content:** `{"id": 12,"serialNo": "SN560","createdDate": "2019-01-08T05:14:08-05:00",...}`
 
* **Error Response:**

  * **Code:** 400 BAD REQUEST <br />
    **Content:** `{"serialNo": ["This value is already used."]}`
    
***Get Devices***

  Show all devices

* **URL**

  /api/devices

* **Method:**

  `GET` 

* **Data Params**

  NONE

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** `[{"serialNumber":"SN123"},{"serialNumber":...}]`

  
***Add Flag***

  Add flag to the device

* **URL**

  /api/flags

* **Method:**

  `POST` 

* **Data Params**

  **Required:** `{"serialNumber":"SN560","flag":"dekompletacja_rozpakowywanie"}`

* **Success Response:**

  * **Code:** 201 <br />
    **Content:** `{"message": "Flag: dekompletacja_rozpakowywanie has been added for device S\/N: 40011"}`
 
* **Error Response:**

  * **Code:** 400 BAD REQUEST <br />
    **Content:** `{"error": "Attempt to assign not existing or not allowed flag"}`
  * **Code:** 404 NOT FOUND <br />
    **Content:** `{"message": "Device with S\/N: 123123123213213 doesn't exist"}`
  
***Get Device Flags***

  Show flags for a device

* **URL**

  /api/devices/[id]/flags

* **Method:**

  `GET` 

* **Data Params**

  NONE

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** `[{"name": "dekompletacja_rozpakowywanie","createdBy": "192.168.56.1","createdDate": "2019-01-08T05:19:58-05:00"},
    {"name": "testowanie_uszkodzony","createdBy": "192.168.56.1","createdDate": "2019-01-08T05:25:04-05:00"}]`
 
* **Error Response:**

  * **Code:** 404 NOT FOUND <br />
    **Content:** `{"code": 404,"message": "No device with ID: 113 found"}`

