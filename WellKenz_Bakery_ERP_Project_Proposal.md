# WellKenz Bakery Enterprise Resource Planning System (WBERPS)

## A Project Proposal
### Presented to
**The College of Computer, Information and Communications Technology**  
**Cebu Technological University – Main Campus**  
**R. Palma St., Cebu City**  
**In Partial Fulfillment of the Requirements for the Subject**  
**System Analysis and Design**

---

**Prepared by:**  
Magdasal, John Mark  
Noquiana, Jhon Paul  
Vender, Roberto  
Arsua, Edrian

---

## RATIONALE

The bakery industry operates within a complex ecosystem of interconnected processes that require precise coordination across inventory management, production planning, procurement, and quality control. Traditional bakery operations often struggle with disconnected systems and manual processes that lead to inefficiencies, material waste, production delays, and reduced profitability. These challenges are particularly pronounced in medium to large-scale bakery operations where multiple departments must coordinate seamlessly to maintain product quality and meet customer demands.

WellKenz Bakery, as a growing bakery operation, faces these industry-wide challenges that require a comprehensive technological solution. The current manual processes for managing ingredient requisitions, purchasing decisions, inventory tracking, production scheduling, and supplier coordination result in inaccurate records, delayed approvals, over-purchasing, stock shortages, and inefficient resource utilization — all of which significantly impact productivity and profitability.

To address these multifaceted challenges, this study aims to design and develop a comprehensive WellKenz Bakery Enterprise Resource Planning System (WBERPS) that automates and integrates the entire bakery operation. The system will centralize and connect all critical business processes including inventory management, production planning, procurement, supplier management, financial tracking, and workflow management into one unified platform.

By implementing WBERPS, bakery staff can efficiently request materials through automated requisitions, supervisors can approve and oversee operations with real-time visibility, purchasing officers can optimize supplier relationships and procurement processes, inventory managers can maintain accurate stock levels with batch tracking and expiry management, and production staff can follow standardized recipes and track consumption. This comprehensive integration will significantly improve coordination, transparency, and accountability across all departments while supporting data-driven decision making for sustainable business growth.

## OBJECTIVES OF THE STUDY

### General Objective
The main objective of this study is to design and develop a comprehensive WellKenz Bakery Enterprise Resource Planning System (WBERPS) that automates and integrates all core bakery operations including inventory management, production planning, procurement, supplier management, and financial tracking to improve operational efficiency, reduce waste, and enhance business profitability.

### Specific Objectives
Specifically, the system aims to:

1. **Manage the following comprehensive modules:**
   - **User Management System** – to manage user accounts, roles, and permissions across five distinct levels (Administrator, Supervisor, Purchasing Officer, Inventory Manager, and Production Staff/Baker)
   - **Supplier Management System** – to maintain comprehensive supplier details, contact information, pricing agreements, performance ratings, and payment terms
   - **Item Master and Categories Management** – to organize raw materials, finished goods, semi-finished products, and supplies with detailed categories, units of measurement, and specifications
   - **Inventory Management System** – to track real-time stock levels, manage inventory batches with expiry dates, monitor stock movements, and provide automated low-stock and expiry alerts
   - **Production Management System** – to manage standardized recipes, plan and execute production orders, track material consumption, and monitor yield efficiency
   - **Procurement Management System** – to handle purchase requests, generate and process purchase orders, manage supplier selection, and track deliveries
   - **Requisition Management System** – to process internal material requests between departments with multi-level approval workflows and status tracking
   - **Notification and Alert System** – to provide real-time alerts for approvals, deliveries, low-stock levels, expiring items, and critical system activities

2. **Generate comprehensive transactions in terms of:**
   - **Purchase Request Workflow** – creation, approval, and tracking of material requests with estimated costs, priority levels, and multi-level approval processes
   - **Purchase Order Processing** – complete lifecycle from creation to supplier communication, delivery tracking, and completion
   - **Inventory Stock Transactions** – comprehensive stock-in and stock-out activities, stock adjustments, inter-location transfers, waste tracking, and return-to-vendor processes
   - **Production Order Management** – detailed planning, execution tracking, material consumption recording, and completion documentation
   - **Requisition Processing** – complete workflow from creation to fulfillment with real-time status tracking and automated notifications
   - **Batch Management Operations** – creation, tracking, movement, and disposal of inventory batches with comprehensive expiry date management

3. **Generate comprehensive reports in terms of:**
   - **Inventory Analytics** – current stock summaries, detailed stock movement history, expiry reports, inventory valuation, and stock level analysis
   - **Procurement Intelligence** – purchase history analysis, supplier performance metrics, cost analysis, delivery performance tracking, and spending patterns
   - **Production Analysis** – yield variance reports, material consumption analysis, production efficiency metrics, and Cost of Goods Sold (COGS) calculations
   - **Financial Summaries** – purchase order summaries, inventory cost analysis, supplier payment analysis, and profitability tracking reports
   - **Compliance and Audit Reports** – comprehensive system activity logs, user action tracking, approval workflows, and regulatory compliance documentation

4. **Provide advanced functionality including:**
   - **Real-time Stock Tracking and Synchronization** – with automated stock updates from all transaction types across the system
   - **Complete Batch Traceability** – full product lifecycle tracking from raw material procurement through production to finished goods delivery
   - **Proactive Expiry Management** – automated alerts and management system for expiring inventory to minimize waste
   - **Accurate Cost Tracking and Allocation** – precise cost tracking across all operations with real-time calculations
   - **Configurable Multi-level Approval Workflows** – flexible approval processes for different transaction types and organizational structures
   - **Comprehensive Role-based Access Control** – secure access to system functions with detailed permissions based on user roles and responsibilities
   - **Automated Calculations and Data Processing** – real-time total calculations, stock adjustments, cost computations, and financial summaries

## SCOPE AND LIMITATIONS

### Scope of Implementation
The WellKenz Bakery ERP System will encompass:
- Complete enterprise-level inventory management with real-time tracking and comprehensive batch management
- Integrated procurement lifecycle from initial purchase requests through supplier payment processing
- Comprehensive production planning and execution management with standardized recipe handling
- Detailed supplier management with performance tracking and relationship management
- Advanced reporting and business intelligence capabilities across all modules
- Comprehensive user management with granular role-based access control
- Sophisticated notification and alert systems with configurable triggers
- Audit trails and compliance tracking for all system activities

### System Limitations
The system will not include:
- Customer Relationship Management (CRM) features for retail customer management
- Point-of-sale (POS) integration for direct customer transactions
- Advanced financial accounting beyond procurement and inventory cost tracking
- E-commerce platform integration for online sales
- Mobile native applications (designed for web-based interface only)
- Integration with third-party external accounting or ERP systems
- Sales forecasting and demand planning beyond basic production scheduling

## EXPECTED BENEFITS AND IMPACT

### Operational Benefits
- **Dramatically Improved Inventory Accuracy** – Real-time tracking eliminates manual errors and significantly reduces stock discrepancies across all categories
- **Enhanced Production Efficiency** – Integrated production planning and standardized recipes optimize resource utilization and reduce waste
- **Streamlined Supplier Management** – Automated procurement processes and comprehensive supplier performance tracking improve vendor relationships
- **Minimized Material Waste** – Better inventory management, expiry tracking, and batch management significantly reduce spoilage and overstock situations

### Business Impact
- **Substantial Cost Reduction** – Improved inventory control, reduced waste, and enhanced supplier negotiation capabilities through comprehensive data analysis
- **Increased Overall Profitability** – Better cost tracking, operational efficiency, and waste reduction directly impact bottom-line results
- **Enhanced Decision-Making Capabilities** – Real-time data access and comprehensive reporting support informed strategic and operational decisions
- **Improved Regulatory Compliance** – Complete audit trails, batch traceability, and documentation ensure regulatory compliance and quality standards

## TECHNICAL IMPLEMENTATION APPROACH

### System Architecture and Technology Stack
The WellKenz Bakery ERP System will be implemented using:
- **Backend Framework**: Laravel PHP framework for robust server-side functionality, security, and scalability
- **Frontend Technology**: Blade templating engine with modern CSS and JavaScript for responsive, user-friendly interfaces
- **Database Management**: PostgreSQL for reliable data storage, complex query handling, and transaction integrity
- **Authentication and Security**: Laravel's built-in authentication system enhanced with comprehensive role-based access control
- **Deployment Platform**: Web-based deployment optimized for bakery operational environments with cross-platform compatibility

### Key System Features
- **Scalable Enterprise Architecture** – Supports multiple bakery locations and growing business operations without system redesign
- **Intuitive User Interface Design** – Specialized design optimized for bakery operations with minimal training requirements
- **Real-time Data Synchronization** – Instant updates across all modules and user interfaces for accurate decision making
- **Comprehensive Data Security** – Multi-layered security with role-based access control, audit logging, and data encryption
- **Advanced Reporting and Analytics** – Comprehensive business intelligence reports for strategic planning and operational optimization

## CONCLUSION

The WellKenz Bakery Enterprise Resource Planning System represents a comprehensive technological solution to the complex, interconnected challenges faced by modern bakery operations. By integrating all critical business processes into a unified, automated platform, the system will fundamentally transform operational efficiency, significantly reduce costs, and enhance decision-making capabilities across the entire organization.

The successful implementation of WBERPS will position WellKenz Bakery as a modern, technology-driven operation capable of competing effectively in today's challenging business environment while maintaining and enhancing the quality and consistency that customers expect from premium bakery products. This system will serve as a foundation for sustainable growth and operational excellence in the competitive bakery industry.

---

**Keywords:** Enterprise Resource Planning, Bakery Management System, Inventory Control, Production Planning, Supplier Management, Recipe Management, Batch Tracking, Laravel Framework, Web-based ERP

**Date:** November 2025