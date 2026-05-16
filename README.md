# NetLearn Module: Subnetting & IP Visualization

**Overview**
This repository module contains the specific feature implementation for **IPv4 Subnetting (VLSM)** and **IPv6 Address Visualization**. This component is designed to be integrated into the larger **NetLearn** educational platform, serving as an interactive tool for networking students.

This documentation serves as a technical breakdown of the module's architecture, underlying logic, and file structures for evaluation and review purposes.

## Architectural Approach
To ensure the module remains maintainable and integrates smoothly into the larger NetLearn ecosystem, the logic is separated into a clean Client-Server (Frontend/Backend) architecture using PHP and Javascript:
- **Frontend (UI & Interaction):** Built using Tailwind CSS for rapid styling and vanilla JavaScript to handle user inputs asynchronously without page reloads.
- **Backend (API & Logic):** Handled via a modular PHP architecture. Core mathematical calculations are kept strictly in PHP to ensure accuracy and to make the logic reusable across different parts of the platform if needed.

## File Structure & Component Logic

### 1. `src/index.php` (The Frontend Interface)
This file represents the UI component that will eventually be embedded or linked within the main NetLearn application.
- **Dual Mode UI:** Allows users to seamlessly switch between the IPv4 Calculator and the IPv6 Explorer.
- **Asynchronous Processing (AJAX):** Uses the JavaScript Fetch API to collect user parameters (e.g., base IP, prefix, requested subnets) and post them to our PHP backend (`api.php`).
- **Dynamic Rendering:** Parses the JSON response from the backend and dynamically constructs the Subnet Table and calculation steps in the DOM.

### 2. `src/api.php` (The Bridge / API Controller)
Acting as the middleman, this file receives requests from the frontend and delegates tasks to the appropriate PHP utility classes.
- Ensures the front end and backend calculations remain decoupled.
- Handles two main actions: `generate` (for randomizing an IP) and `calculate` (for processing VLSM).
- Packages all output and error handling into standard JSON format.

### 3. `src/SubnetCalculator.php` (The VLSM Engine)
This is the core computational brain of the IPv4 feature, dedicated strictly to implementing Variable Length Subnet Masking (VLSM) rules accurately.
- **Requirement Sorting:** Automatically sorts user host requests in descending order, a critical rule in VLSM to prevent subnet overlapping.
- **Subnet Math:** Uses logarithmic functions to calculate the necessary host bits and determines the appropriate CIDR prefix.
- **Bitwise Logic:** Converts IP strings into 32-bit long integers (`ip2long()`). This allows the system to accurately determine network boundaries, broadcast addresses, and the next usable subnet block using pure arithmetic.
- **Overflow Validation:** Checks if the sum of requested subnets exceeds the available space of the base network. If it does, the logic triggers a suggestion engine to recommend the minimum required CIDR prefix to the user.

### 4. `src/IpUtils.php` (IP Randomizer Logic)
A supplementary utility to improve user experience by generating valid random IP addresses.
- Respects standard IPv4 network classes (A, B, C) by bounding the randomization of the first octet.
- Provides a quick starting point for students who want to test the calculator without manually typing network parameters.

### 5. `src/Ipv6Helper.php` (IPv6 SLA Visualization)
This utility powers the IPv6 Explorer component of the module.
- Demonstrates how the 128-bit IPv6 address is segmented.
- Takes the user's input from the frontend slider (0 to 65,535) and converts it to its correct 4-character Hexadecimal equivalent (SLA ID) using PHP's base conversion and string padding functions.

## Integration Note
Since this is a standalone feature for the broader NetLearn platform, the styling (Tailwind via CDN) and routing logic in `index.php` are designed modularly. This ensures it can be easily ported and adapted into the master NetLearn template once the main system architecture is finalized.